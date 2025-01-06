export default class CuracaoGCB {
    constructor(configs) {
        this.gcbLink = configs.gcb_link ? configs.gcb_link : '';
        this.gcbDomainMapping = configs.gcb_domain_mapping ? configs.gcb_domain_mapping : [];
        this.hostname = window.location.hostname;
        this.domainToken = Object.keys(this.gcbDomainMapping).find(key => this.hostname === key || this.hostname.endsWith('.' + key));


        this.renderImage();
    }

    renderImage() {
        const curacaoContainerEl = document.querySelector('#curacao-container');
        if (curacaoContainerEl === null || this.gcbLink === '' || typeof this.domainToken === 'undefined') {
            return;
        }
        curacaoContainerEl.querySelector('a').href = this.gcbLink.replace('{gcb_token}', this.gcbDomainMapping[this.domainToken]);

        curacaoContainerEl.classList.remove('hidden');
    }

}
