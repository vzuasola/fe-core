/**
 * Interface which defines what a component is
 */
export interface ComponentInterface {
    onLoad(element: HTMLElement, attachments: {});
    onReload(element: HTMLElement, attachments: {});
}
