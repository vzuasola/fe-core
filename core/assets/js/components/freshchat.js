/**
 * Freshchat integration
 */

export default function Freshchat(attachments) {
    /**
     * Get player details
     */
    var player = attachments.player_details;

    /**
     * Get Freshchat enability
     */
    var freshchatEnable = parseInt(attachments.freshchat_enability);

    /**
     * Get Freshchat token
     */
    var freshchatToken = attachments.freschat_token;

    /**
     * Set Freshchat user properties
     */
    var userProperties = {
        externalId: (player ? player.icore_id : 'Anonymous'),
        firstName: (player ? player.first_name : 'Anonymous'),
        lastName: (player ? player.last_name : 'Anonymous'),
        email: (player ? player.email : 'Anonymous'),
    };

    /**
     * App logo
     */
    var appLogo = attachments.configs.app_logo || [];

    /**
     * Background logo
     */
    var bgLogo = attachments.configs.bg_img || [];


    /**
     * Header configuration
     */
    var freshchatHeaders = {
        chat: attachments.configs.chat_header || 'Chat',
        chat_help: attachments.configs.chat_description || 'Reach out to us for any concerns',
        faq: attachments.configs.faq_title || 'FAQ',
        faq_help: attachments.configs.faq_description || 'Browse our articles',
        faq_not_available: attachments.configs.faq_not_available || 'No articles found',
        faq_search_not_available: attachments.configs.faq_no_results || 'No articles found for {{query}}',
        faq_useful: attachments.configs.faq_helpful || 'Was this helpful?',
        faq_thankyou: attachments.configs.faq_thankyou || 'Thank you for your feedback',
        faq_message_us: attachments.configs.faq_message_us || 'Message us',
        push_notification: attachments.configs.push_allow || 'Allow push notifications?',
        csat_question: attachments.configs.csat_question || 'Did we adress your concerns?',
        csat_no_question: attachments.configs.csat_no_question || 'How could we have helped better?',
        csat_thankyou: attachments.configs.csat_thankyou_msg || 'Thank you for the response',
    };

    /**
     * Action configurations
     */
    var freshchatActions = {
        csat_yes: attachments.configs.csat_action_yes || 'Yes',
        csat_no: attachments.configs.csat_action_no || 'No',
        push_notify_yes: attachments.configs.push_yes_button || 'Yes',
        push_notify_no: attachments.configs.push_no_button || 'No',
        tab_faq: attachments.configs.faq_tab || 'FAQ',
        tab_chat: attachments.configs.chat_tab || 'Chat',
    };

    /**
     * Placeholders configuration
     */
    var freshchatPlaceholders = {
        search_field: attachments.configs.faq_search || 'Search',
        reply_field: attachments.configs.chat_placeholder || 'Reply',
        csat_reply: attachments.configs.faq_comment || 'Add your comments here',
    };

    /**
     * Agent configuration
     */
    var freshchatAgent = {
        hideName: parseInt(attachments.configs.agent_hide_name) || 0,
        hidePic: parseInt(attachments.configs.agent_hide_pic) || 0,
    };

    /**
     * Set Freshchat configuration / customization
     */
    var freshchatConfig = {
        content: {
            placeholders: freshchatPlaceholders,
            actions: freshchatActions,
            headers: freshchatHeaders,
        },
        showFAQOnOpen: parseInt(attachments.configs.faq_show_on_open) || 0,
        hideFAQ: parseInt(attachments.configs.faq_show) || 0,
        agent: freshchatAgent,
        cssNames: {
            widget: 'fc_frame',
            open: 'fc_open',
            expanded: 'fc_expanded'
        },
        headerProperty: {
            appName: attachments.configs.app_name || '',
            appLogo: appLogo.length === 0 ? '' : attachments.configs.app_logo_url,
            backgroundImage: bgLogo.length === 0 ? '' : attachments.configs.bg_img_url,
            foregroundColor: attachments.configs.fg_color || '',
            direction: attachments.configs.direction || 'rtl',
        }
    };

    /**
     * Initialization
     */
    function initFreshChat() {
        if (freshchatEnable) {
            window.fcWidget.user.setProperties(userProperties);
            window.fcWidget.init({
                token: freshchatToken,
                host: attachments.configs.host || '',
                siteId: attachments.configs.site_id || '',
                config: freshchatConfig,
            });
        }
    }

    ( function (d, id) {
        var fcJS;
        if (d.getElementById(id)) {
            initFreshChat();
            return;
        }
        fcJS = d.createElement('script');
        fcJS.id = id;
        fcJS.async = true;
        fcJS.src = 'https://wchat.freshchat.com/js/widget.js';
        fcJS.onload = initFreshChat;
        d.head.appendChild(fcJS);
    }(document, 'freshchat-js-sdk'));
}
