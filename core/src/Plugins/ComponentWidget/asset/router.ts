import * as utility from "@core/assets/js/components/utility";
import * as Navigo from "@core/assets/js/vendor/navigo";
import * as xhr from "@core/assets/js/vendor/reqwest";

import {ComponentManager} from "@plugins/ComponentWidget/asset/component";

class Router {
    static beforeNavigate: string = "routerbeforenavigate";
    static afterNavigate: string = "routerafternavigate";
    static navigateError: string = "routererrornavigate";

    private router = new Navigo();

    // List all the available options for the router
    private options = {
        // flag if scroll will be on top on navigate
        "scroll-to-top": true,

        // pass a callback that will post process the url generators
        //
        // Router.setOption("process-url-generators", (url: string, type: string) => {
        //     return url;
        // })
        "process-url-generators": (url: string, type: string) => {
            return url;
        },

        // Specify the main parent components
        "main-components": ["main"],

        // Enable adding of active class
        "router-active-link-class": true,
    };

    private componentStack: string[] = [];

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
     * Generates a route to the component controller
     *
     * @param {string} id
     * @param {string} method
     */
    generateRoute(id, method, options: any = {}) {
        let language = this.getLanguage();

        if (options.lang) {
            language = options.lang;
        }

        let url = `/${language}/api/plugins/component/route/${id}/${method}`;

        url = this.addQueryParams(url);

        return this.options["process-url-generators"](url, "component");
    }

    /**
     * Generates a route to the module controller
     *
     * @param {string} id
     * @param {string} method
     */
    generateModuleRoute(id, method) {
        const language = this.getLanguage();
        let url = `/${language}/api/plugins/module/route/${id}/${method}`;

        url = this.addQueryParams(url);

        return this.options["process-url-generators"](url, "module");
    }

    /**
     * Generates a route to the module list
     */
    generateModuleListRoute() {
        const language = this.getLanguage();
        let url = `/${language}/api/plugins/module`;

        const wbcTokenCookie = document.cookie
                                .split("; ")
                                .filter((row) => row.lastIndexOf("wbcToken=",0) === 0)

        const userLoggedIn = wbcTokenCookie[0] ? true : false;

        if(userLoggedIn){
            const decodedCookie = utility.parseJwt(wbcTokenCookie[0]);
            const token = decodedCookie['sessionToken'] ? decodedCookie['sessionToken'] : (Math.floor(Math.random() * 99999999) + 1);
            const cacheKey = btoa(token)
            url += '/' + cacheKey;
        }

        url = this.addQueryParams(url);

        return this.options["process-url-generators"](url, "module-list");
    }

    /**
     * Generates a route to the component renderer
     *
     * @param {string} id
     * @param {string} path
     */
    generateRenderRoute(id, path = window.location.pathname + window.location.search) {
        path = utility.addQueryParam(path, "component-data-widget", id);

        return this.options["process-url-generators"](path, "render");
    }

    /**
     * Initializes this module
     */
    init() {
        this.activateListener();
        this.listenHistoryChanges();
    }

    /**
     * Navigate to a certain URL
     *
     * @param {string} url
     * @param {string[]} components
     */
    navigate(url: string, components: string[], options?: {[name: string]: any}) {
        // support for reload all components
        if (components.indexOf("*") !== -1) {
            components = this.options["main-components"];
        }

        // allow current query parameters to be passed to the router navigation
        url = this.addQueryParams(url);

        if (options && typeof options.removeParams !== "undefined") {
            for (const param of options.removeParams) {
                url = utility.removeQueryParam(url, param);
            }
        }

        this.componentStack = this.componentStack.concat(components);

        utility.invoke(document, Router.beforeNavigate, {
            components,
            url,
        });

        if (options && typeof options.language !== "undefined") {
            this.setLanguage(options.language);
        }

        if (this.getNavigationMode() === 'reload') {
            window.location.href = url;
            return;
        }

        ComponentManager.refreshComponents(
            components,
            (response) => {
                // check to see if request is valid
                if (options && typeof options["is-request-valid"] !== "undefined") {
                    if (!options["is-request-valid"](url, response)) {
                        return;
                    }
                }

                // set the document title
                document.title = response.querySelector("title").innerHTML;

                const body = response.querySelector("body");

                const attributes = body.dataset;
                for (const attribute in attributes) {
                    if (attributes.hasOwnProperty(attribute)) {
                        document.body.setAttribute("data-" + attribute, attributes[attribute]);
                    }
                }

                // add product to body class
                const bodyClass = document.body.className;
                document.body.className = bodyClass.replace(/mobile-[a-z\-_]*/, 
                    document.body.getAttribute("data-product"));

                // update the server path
                this.setRoute(body.getAttribute("data-route"));

                // flag to check if router navigation should be allowed
                if (options &&
                    typeof options["disable-navigate"] !== "undefined" &&
                    options["disable-navigate"] === true
                ) {
                    // do nothing
                } else {
                    this.router.navigate(url, true, {
                        components,
                        url,
                    });
                }

                if (this.options["scroll-to-top"]) {
                    window.scrollTo(0, 0);
                }

                utility.invoke(document, Router.afterNavigate, {
                    components,
                    url,
                });

                this.addActiveClass(document.body);
            },
            (error, message, response) => {
                utility.invoke(document, Router.navigateError, {
                    components,
                    url,
                    response,
                });
            },
            url,
        );
    }

    /**
     * Listens to a router event
     *
     * @param {string} event The event name
     */
    on(event: string, handler) {
        utility.listen(document, event, handler);
    }

    /**
     * Gets the current language
     */
    getLanguage() {
        return document.body.getAttribute("data-language");
    }

    /**
     * Gets the current navigation mode
     */
    getNavigationMode() {
        return document.body.getAttribute("data-navigation-mode");
    }

    /**
     * Gets the current server path
     */
    route() {
        return document.body.getAttribute("data-route");
    }

    /**
     * Sets the current language
     */
    setLanguage(language: string) {
        document.body.setAttribute("data-language", language);
    }

    /**
     * Sets the current server path
     */
    setRoute(path: string) {
        document.body.setAttribute("data-route", path);
    }

    /**
     *
     */
    private activateListener() {

        utility.listen(document, "components.early.finish", (event, target) => {
            this.addActiveClass(document.body);
        });

        utility.listen(document, "components.reload", (event, src, data) => {
            this.activateMenuLink(data.stack);
        });

        utility.listen(document, "click", (event, target) => {
            if (target.tagName !== "A") {
                target = utility.findParent(target, "A".toLowerCase(), 5);
            }

            if (target &&
                target.tagName === "A" &&
                target.getAttribute("data-router")
            ) {
                let components = [];

                const href: string = target.getAttribute("href");
                const refresh: string = target.getAttribute("data-router-refresh");

                // router will ignore links with defined targets
                if (target.getAttribute("target")) {
                    return;
                }

                // ignore fragments
                if (href.indexOf("#") === 0) {
                    return;
                }

                // only process relative URLs
                if (!utility.isExternal(href)) {
                    event.preventDefault();

                    try {
                        components = JSON.parse(refresh);
                    } catch (error) {
                        components = [refresh];
                    }

                    if (href && components && components.length > 0) {
                        try {
                            this.navigate(href, components);
                        } catch (error) {
                            // something happened with the router
                            window.location.replace(href);
                        }
                    }
                }
            }
        });
    }

    /**
     * Listen for history changes and reload components dynamically
     */
    private listenHistoryChanges() {
        window.onpopstate = (event) => {
            if (event.state && typeof event.state.url !== "undefined") {
                this.navigate(
                    event.state.url,
                    this.options["main-components"],
                    {
                        "disable-navigate": true,
                        "is-request-valid": (url, response) => {
                            return window.location.href === utility.toAbsolute(event.state.url);
                        },
                    },
                );
            }
        };
    }

    private addQueryParams(url) {
        // allow current query parameters to be passed to the router navigation
        const params = utility.getParameters(window.location.search);

        // List of parameters that shouldn't be included on the query parameters
        const blockedParams = ['password'];

        for (const key in params) {
            if ((key !== "")
                && (blockedParams.indexOf(key) < 0)
                && (params[key] !== "")) {
                url = utility.addQueryParam(url, key, params[key]);
            }
        }

        return url;
    }

    /**
     * Adds active class to anchor when components refresh
     */
    private activateMenuLink(ids) {
        if (this.options["router-active-link-class"]) {
            for (const id in ids) {
                if (ids.hasOwnProperty(id)) {
                    this.addActiveClass(ComponentManager.getComponent(ids[id]));
                }
            }
        }
    }

    /**
     * Adds active class to the anchortag
     */
    private addActiveClass(element: HTMLElement) {
        const menus: NodeListOf<HTMLElement> = element
            .querySelectorAll("[data-router-active-link-class]");
        if (menus) {
            for (const key in menus) {
                if (menus.hasOwnProperty(key)) {
                    const menu: HTMLElement = menus[key];
                    const url = utility.removeHash(menu.getAttribute("href"));

                    utility.removeClass(menu, menu.getAttribute("data-router-active-link-class"));
                    if (utility.trimEnd(url, "/") === utility.trimEnd(window.location.pathname, "/")) {
                        utility.addClass(menu, menu.getAttribute("data-router-active-link-class"));
                    }
                }
            }
        }
    }
}

const router = new Router();

export {Router as RouterClass};
export {router as Router};
