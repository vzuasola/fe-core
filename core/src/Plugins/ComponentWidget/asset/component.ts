import * as utility from "@core/assets/js/components/utility";
import * as xhr from "@core/assets/js/vendor/reqwest";

import {Router} from "@plugins/ComponentWidget/asset/router";

import {ComponentInterface} from "./component.interface";
import {ModuleInterface} from "./module.interface";

class ComponentManager {
    private lateScriptStatus = 'notUsed';
    private components: {[name: string]: ComponentInterface} = {};
    private modules: {[name: string]: ModuleInterface} = {};
    private events: any = {};

    // List all the available options for the component manager
    private options = {
        "module-response-handle-redirect": (request) => {
            return false;
        },
        "module-response-handle-error": (request) => {
            // do something on the callback
        },
    };

    /**
     * Sets an option to control the router behavior
     *
     * @param {string} key
     * @param {any} option
     */
    setOption(key: string, option: any) {
        this.options[key] = option;
    }

    /**
     * Register a list of components to the component manager
     *
     * @param {object} components Collection of components
     */
    setComponents(components: {[name: string]: ComponentInterface}) {
        // @ts-ignore
        this.components = Object.assign({}, components);
    }

    addComponent(name: string, component: ComponentInterface) {
        this.components[name] = component;
    }

    setLateScriptStatus(status) {
        this.lateScriptStatus = status;
    }

    setAndInitLateComponent(lateComponents) {
        var lateSync = [];

        // Add the late components to the ComponentManager and find the late components that
        // need sync loading. The async ones (the ones that need rendering) will be automatically
        // discovered due to their mode attribute.
        for (const key in lateComponents) {

            this.addComponent(key, lateComponents[key]);

            // Find the HTML wrapper element of this component
            let element: HTMLElement = document.querySelector(`[data-component-widget-class="${key}"]`);
            if (!element) {
                continue;
            }

            const mode = element.getAttribute("data-component-widget-mode");

            if (mode !== "prerender") {
                lateSync.push(key);
            }
        }

        this.doInitComponentsAsync();
        this.doInitSyncComponents(lateSync, document);
    }

    /**
     * Register a list of modules to the component manager
     *
     * @param {object} modules Collection of modules
     */
    setModules(modules: {[name: string]: ModuleInterface}) {
        this.modules = modules;
    }

    /**
     * Initializes this module
     */
    init() {
        utility.ready(() => {
            this.doLoadModules(() => {
                this.doInitComponentsAsync();
                this.doInitComponents();
            });
        });
    }

    loadLateScript() {
        const lateScriptDiv = document.getElementById('lateScriptDiv');
        if (lateScriptDiv) {
            const lateScriptUrl = lateScriptDiv.getAttribute('data-script');

            let lateScript = document.createElement("script");
            lateScript.defer = true;
            lateScript.setAttribute("src", lateScriptUrl);

            document.body.appendChild(lateScript);

            lateScript.addEventListener("error", (ev) => {
                console.log("Error on loading late script", ev);
            });
        }
    }

    /**
     * Components
     *
     */

    /**
     * Get list of all components
     *
     * @returns {object}
     */
    getComponents() {
        return this.components;
    }

    getComponentInstance(id: string) {
        return this.components[id];
    }

    /**
     * Gets an array of active components
     */
    getActiveComponents() {
        const list = [];
        const items = this.getComponents();
        const widgets: NodeListOf<HTMLElement> = document.querySelectorAll("[data-component-widget-class]");

        for (const key in widgets) {
            if (widgets.hasOwnProperty(key)) {
                const element = widgets[key];
                const id = element.getAttribute("data-component-widget-alias");

                if (list.indexOf(id) === -1) {
                    list.push(id);
                }
            }
        }

        return list;
    }

    /**
     * Gets an array of rendered components
     */
    getRenderedComponents() {
        const list = [];
        const items = this.getComponents();
        const widgets: NodeListOf<HTMLElement> = document.querySelectorAll("[data-component-widget-class]");

        for (const key in widgets) {
            if (widgets.hasOwnProperty(key)) {
                const element = widgets[key];
                const id = element.getAttribute("data-component-widget-alias");
                const mode = element.getAttribute("data-component-widget-mode");

                if (list.indexOf(id) === -1 && mode !== "prerender") {
                    list.push(id);
                }
            }
        }

        return list;
    }

    /**
     * Gets an array of active unrenderd components
     */
    getUnderenderedComponents() {
        const list = [];
        const items = this.getComponents();
        const widgets: NodeListOf<HTMLElement> = document.querySelectorAll("[data-component-widget-class]");

        for (const key in widgets) {
            if (widgets.hasOwnProperty(key)) {
                const element = widgets[key];
                const id = element.getAttribute("data-component-widget-alias");
                const elementClass = element.getAttribute("data-component-widget-class");
                const mode = element.getAttribute("data-component-widget-mode");

                // We skip all widgets that are already included or they do not need rendering or the
                // corresponding component is not added to ComponentManager (e.g because it will be
                // loaded through late.js)
                if (list.indexOf(id) === -1 && items[elementClass] && mode === "prerender") {
                    list.push(id);
                }
            }
        }

        return list;
    }

    /**
     * Gets a single component element
     *
     * @param {string} id The component ID
     * @param {HTMLDocument} wrapper = document The HTML document to bind with
     */
    getComponent(id, wrapper = document) {
        let element: HTMLElement = wrapper.querySelector(`[data-component-widget-class="${id}"]`);

        // alias fallback support
        if (!element) {
            element = wrapper.querySelector(`[data-component-widget-alias="${id}"]`);
        }

        return element;
    }

    /**
     * Load Components
     *
     */

    /**
     * Loads a single component
     *
     * @param {string} id
     */
    doLoadComponent(id) {
        const items: {[name: string]: ComponentInterface} = this.getComponents();
        const element = this.getComponent(id);

        if (element) {
            const attachments = this.getAttachment(element);
            items[id].onLoad(element, attachments);
        }
    }

    /**
     * Loads all component
     *
     * @param {HTMLDocument} wrapper = document The HTML document to bind with
     */
    doInitComponents(wrapper = document) {
        // Find the components that have a wrapper in main HTML page and they are meant to be rendered on browser side.
        const widgets = this.getRenderedComponents();

        this.doInitSyncComponents(widgets, wrapper);
    }

    doInitSyncComponents(widgets, wrapper = document) {
        const items = this.getComponents();
        const stack = [];

        for (const id of widgets) {
            const element = this.getComponent(id);
            const attachments = this.getAttachment(element);

            const alias = element.getAttribute("data-component-widget-class");
            if (alias in items) {
                stack.push(alias);
                items[alias].onLoad(element, attachments);
            }
        }

        const isPendingComponents = this.getUnderenderedComponents();

        this.broadcast("components.load", {
            stack,
        });

        // If async components have already finished loading, all components are loaded.
        // Except if this function has been called by late.js, so main components are
        // already loaded and this event has already been broadcasted.
        if (isPendingComponents.length === 0) {
            if (this.lateScriptStatus === "pending") {
                this.broadcast("components.early.finish");
            } else {
                this.broadcast("components.finish");
            }
        }

        // allows browser to remember my initial history
        history.replaceState(
            {
                components: stack,
                url: window.location.href,
            },
            "",
            window.location.href,
        );
    }

    doInitComponentsAsync(success?) {
        const ids = this.getUnderenderedComponents();
        let markup;
        const reload: Array<{ id: string, body: HTMLElement }> = [];

        const list = ids.slice(0);

        for (const id of ids) {
            const url = Router.generateRenderRoute(id);
            xhr({
                url,
            }).then((response) => {
                const html: any = this.getDomFromString(response);
                const body: HTMLElement = this.getComponent(id, html);

                if (body) {
                    // Gather all component HTML templates
                    reload.push({
                        id,
                        body,
                    });
                }

                if (html) {
                    markup = html;
                }

                // When all component templates are loaded (we have the response for all AJAX requests)
                this.checkComponentState(list, id, () => {
                    if (success) {
                        success(markup);
                    }

                    let stack = [];
                    // For each component that needs reloading (we have HTML response)
                    for (const item of reload) {
                        const element: HTMLElement = this.getComponent(item.id);

                        if (element) {
                            // Replace the component wrapper with the retrieved HTML template
                            // In this template, the wrapper mode is changed into "render"
                            element.outerHTML = item.body.outerHTML;
                            stack = stack.concat(
                                this.doLoadComponentsAsync(this.getComponent(item.id)),
                            );
                        }
                    }

                    if (stack.length > 0) {
                        this.broadcast("components.load", {
                            stack,
                        });
                    }

                    if (this.lateScriptStatus === "notUsed" || this.lateScriptStatus === "pending") {
                        this.broadcast("components.early.finish");
                    }

                    if (this.lateScriptStatus === "notUsed" || this.lateScriptStatus === "loaded") {
                        this.broadcast("components.finish");
                    }

                    if (this.lateScriptStatus === "pending") {
                        this.loadLateScript();
                    }
                });
            }).fail((error, message) => {
                // handle errors
            });
        }
    }

    // Call onLoad() for the async component with retrieved template
    doLoadComponentAsync(element: any) {
        const stack = [];

        const items = this.getComponents();
        const id = element.getAttribute("data-component-widget-class");
        const alias = element.getAttribute("data-component-widget-alias");

        if (id && id in items) {
            const attachments = this.getAttachment(element);
            items[id].onLoad(element, attachments);
            stack.push(id);
        }

        return stack;
    }

    /**
     *
     */
    doLoadComponentsAsync(wrapper: any = document) {
        const stack = this.doLoadComponentAsync(wrapper);

        const items = this.getComponents();
        const widgets: NodeListOf<HTMLElement> = wrapper.querySelectorAll("[data-component-widget-class]");

        // If there are more (new) widget wrappers after loading the component template (and replacing the original
        // wrapper), initialize them by calling the onLoad() function of those components.

        // tslint:disable-next-line:prefer-for-of
        for (let i = 0; i < widgets.length; ++i) {
            const id = widgets[i].getAttribute("data-component-widget-class");

            if (id in items) {
                const attachments = this.getAttachment(widgets[i]);
                stack.push(id);
                items[id].onLoad(widgets[i], attachments);
            }
        }

        return stack;
    }

    /**
     * Reload Components
     *
     */

    /**
     * Reload a component
     *
     * @param {HTMLDocument} element = the component element
     */
    doReloadComponent(element: HTMLElement) {
        const stack = [];

        const items = this.getComponents();
        const id = element.getAttribute("data-component-widget-class");

        if (id && id in items) {
            const attachments = this.getAttachment(element);

            items[id].onReload(element, attachments);

            stack.push(id);
        }

        return stack;
    }

    /**
     * Reloads all component
     *
     * @param {HTMLDocument} wrapper = document The HTML document to bind with
     */
    doReloadComponents(wrapper: any = document) {
        const stack = this.doReloadComponent(wrapper);

        const items = this.getComponents();
        const widgets: NodeListOf<HTMLElement> = wrapper.querySelectorAll("[data-component-widget-class]");

        // tslint:disable-next-line:prefer-for-of
        for (let i = 0; i < widgets.length; ++i) {
            const id = widgets[i].getAttribute("data-component-widget-class");

            if (id in items) {
                const attachments = this.getAttachment(widgets[i]);

                stack.push(id);
                items[id].onReload(widgets[i], attachments);
            }
        }

        return stack;
    }

    /**
     * Refresh components
     *
     */

    /**
     * Refreshes multiple components
     *
     * @param {string[]} ids Component IDs
     * @param {function} success Success handler after the component has been reloaded
     * @param {function} fail Executes on refresh error
     * @param {string} path The path to be used when rendering a component
     */
    refreshComponents(ids: string[], success?, fail?, path?) {
        if (Router.getNavigationMode() === 'reload') {
            window.location.reload();
            return;
        }

        // support for reload all components
        if (ids.indexOf("*") !== -1) {
            ids = this.getActiveComponents();
        }

        let markup;
        const reload: Array<{id: string, body: HTMLElement}> = [];

        const list = ids.slice(0);

        for (const id of ids) {
            let url;

            if (path) {
                url = Router.generateRenderRoute(id, path);
            } else {
                url = Router.generateRenderRoute(id);
            }

            const responseObj = xhr({
                url,
            }).then((response) => {
                const html: any = this.getDomFromString(response);
                const body: HTMLElement = this.getComponent(id, html);

                if (body) {
                    reload.push({
                        id,
                        body,
                    });
                }

                if (html) {
                    markup = html;
                }

                if (reload.length === 0 && fail) {
                    return fail(500, 'Invalid response', responseObj);
                }

                this.checkComponentState(list, id, () => {
                    if (success) {
                        success(markup);
                    }

                    let stack = [];

                    for (const item of reload) {
                        const element: HTMLElement = this.getComponent(item.id);

                        if (element) {
                            element.outerHTML = item.body.outerHTML;

                            stack = stack.concat(
                                this.doReloadComponents(this.getComponent(item.id)),
                            );
                        }
                    }

                    if (stack.length > 0) {
                        this.broadcast("components.reload", {
                            stack,
                            response: responseObj,
                        });
                    }
                });
            }).fail((error, message) => {
                if (fail) {
                    fail(error, message, responseObj);
                }
            });
        }
    }

    /**
     * Refreshes a component
     *
     * @param {string} id
     * @param {function} success Success handler after the component has been reloaded
     * @param {function} fail Executes on refresh error
     * @param {string} path The path to be used when rendering a component
     */
    refreshComponent(id, success?, fail?, path?) {
        xhr({
            url: Router.generateRenderRoute(id, path),
        }).then((response) => {
            const html: any = this.getDomFromString(response);

            const element: HTMLElement = this.getComponent(id);
            const body: HTMLElement = this.getComponent(id, html);

            if (body) {
                element.outerHTML = body.outerHTML;

                this.doReloadComponents(this.getComponent(id));

                if (success) {
                    success(html);
                }
            }
        }).fail((error, message) => {
            if (fail) {
                fail(error, message);
            }
        });
    }

    /**
     * Modules
     *
     */

    /**
     * Get list of all modules
     *
     * @returns {object}
     */
    getModules() {
        return this.modules;
    }

    getModuleInstance(id: string) {
        return this.modules[id];
    }

    /**
     * Add fallback/support for responseUrl in lower browsers
     *
     * @returns {string}
     */
    getResponseUrl(request: any, response: any) {
        const { origin } = window.location;
        const requestUrl = request.url.indexOf(origin) !== -1 ? request.url : (origin + request.url) ;

        const responseURL = request.request.responseURL ? request.request.responseURL : requestUrl;

        return responseURL;
    }

    /**
     *
     */
    doLoadModules(success?) {
        const handleRedirect = this.options["module-response-handle-redirect"];
        const handleError = this.options["module-response-handle-error"];

        const request = xhr({
            url: Router.generateModuleListRoute(),
        }).then((response) => {
            // check if request is a redirect
            if (utility.toAbsolute(request.url) !== this.getResponseUrl(request, response)) {
                if (!handleRedirect(request.request)) {
                    return;
                }
            }

            if (response.modules) {
                const items = this.getModules();

                for (const key in response.modules) {
                    if (response.modules.hasOwnProperty(key)) {
                        const module = response.modules[key];

                        if (key in items) {
                            items[key].onLoad(module.component_module_attachments);
                        }
                    }
                }

                if (success) {
                    success();
                }

                return;
            }

            handleError(request.request);
        }).fail((error, message) => {
            handleError(error);
        });
    }

    /**
     * Event Bindings
     *
     */

    /**
     * Listens to a component event
     *
     * @param {string} event The event name
     */
    subscribe(event: string, handler) {
        const handle = utility.listen(document, event, handler);

        utility.invoke(document, "components.subscribe", {
            event,
        });

        if (!this.events[event]) {
            this.events[event] = [];
        }

        this.events[event].push(handle);
    }

    unsubscribe(event: string) {
        if (typeof this.events[event] !== "undefined") {
            for (const handler of this.events[event]) {
                utility.removeEventListener(document, event, handler);
            }
        }
    }

    /**
     * Broadcasts a component event
     *
     * @param {string} event The event name
     */
    broadcast(event: string, data?) {
        utility.invoke(document, "components.broadcast", {
            event,
            data,
        });

        utility.invoke(document, event, data);
    }

    /**
     * Get data attribute of the body
     *
     * @param {string} key
     */
    getAttribute(key: string) {
        return document.body.getAttribute("data-" + key);
    }

    /**
     * Determines if all components have been loaded
     */
    private checkComponentState(list, current, fn) {
        const index = list.indexOf(current);

        if (index > -1) {
            list.splice(index, 1);
        }

        if (list.length === 0) {
            fn();
        }
    }

    /**
     *
     */
    private getAttachment(element) {
        let attachments = {};

        const scripts = element.getAttribute("data-component-widget-attachments");

        if (scripts) {
            attachments = JSON.parse(scripts);
        }

        return attachments;
    }

    /**
     *
     */
    private getDomFromString(body) {
        const element = document.createElement("html");
        element.innerHTML = body;

        return element;
    }
}

const manager = new ComponentManager();
export {manager as ComponentManager, ComponentInterface, ModuleInterface};
