import Element from "Base/drew/element";
import Event from "Base/drew/event";
import Structure from "Base/drew/structure";
import Str from "Base/drew/string";
import Cookie from "Base/drew/cookie";
import Router from "Base/drew/url";
import DateUtil from "Base/drew/date";
import Mobile from "Base/drew/mobile";
import Entity from "Base/drew/entity";

// Elements

export function find() {
    return Element.find.apply(this, arguments);
}

export function hasClass() {
    return Element.hasClass.apply(this, arguments);
}

export function addClass() {
    return Element.addClass.apply(this, arguments);
}

export function removeClass() {
    return Element.removeClass.apply(this, arguments);
}

export function toggleClass() {
    return Element.toggleClass.apply(this, arguments);
}

export function siblings() {
    return Element.siblings.apply(this, arguments);
}

export function findSibling() {
    return Element.findSibling.apply(this, arguments);
}

export function hasCollection() {
    return Element.hasCollection.apply(this, arguments);
}

export function findParent() {
    return Element.findParent.apply(this, arguments);
}

export function isNodeList() {
    return Element.isNodeList.apply(this, arguments);
}

export function nextElementSibling() {
    return Element.nextElementSibling.apply(this, arguments);
}

export function previousElementSibling() {
    return Element.previousElementSibling.apply(this, arguments);
}

export function getAttributes() {
    return Element.getAttributes.apply(this, arguments);
}

export function scrollTo() {
    return Element.scrollTo.apply(this, arguments);
}

export function getCoords() {
    return Element.getCoords.apply(this, arguments);
}

export function prepend() {
    return Element.prepend.apply(this, arguments);
}

export function wrapElement() {
    return Element.wrapElement.apply(this, arguments);
}

export function createElem() {
    return Element.createElem.apply(this, arguments);
}

export function hasAttribute() {
    return Element.hasAttribute.apply(this, arguments);
}

export function removeAttributes() {
    return Element.removeAttributes.apply(this, arguments);
}

export function mergeObjects() {
    return Element.mergeObjects.apply(this, arguments);
}


// Events

export function ready() {
    return Event.ready.apply(this, arguments);
}

export function addEventListener() {
    return Event.addEventListener.apply(this, arguments);
}

export function removeEventListener() {
    return Event.removeEventListener.apply(this, arguments);
}

export function invoke() {
    return Event.invoke.apply(this, arguments);
}

export function listen() {
    return Event.listen.apply(this, arguments);
}

export function delegate() {
    return Event.delegate.apply(this, arguments);
}

export function getTarget() {
    return Event.getTarget.apply(this, arguments);
}

export function preventDefault() {
    return Event.preventDefault.apply(this, arguments);
}

export function triggerEvent() {
    return Event.triggerEvent.apply(this, arguments);
}

export function eventType() {
    return Event.eventType.apply(this, arguments);
}

// Structure

export function forEach() {
    return Structure.forEach.apply(this, arguments);
}

export function isArray() {
    return Structure.isArray.apply(this, arguments);
}

export function forEachElement() {
    return Structure.forEachElement.apply(this, arguments);
}

export function arrayFilter() {
    return Structure.arrayFilter.apply(this, arguments);
}

export function filter() {
    return Structure.filter.apply(this, arguments);
}

export function clone() {
    return Structure.clone.apply(this, arguments);
}

export function isEmptyObject() {
    return Structure.isEmptyObject.apply(this, arguments);
}

export function empty() {
    return Structure.empty.apply(this, arguments);
}

export function append() {
    return Structure.append.apply(this, arguments);
}

export function serialize() {
    return Structure.serialize.apply(this, arguments);
}

export function inArray() {
    return Structure.inArray.apply(this, arguments);
}

// Strings

export function trim() {
    return Str.trim.apply(this, arguments);
}

export function replaceStringTokens() {
    return Str.replaceStringTokens.apply(this, arguments);
}

export function getAsciiSum() {
    return Str.getAsciiSum.apply(this, arguments);
}

export function trimEnd() {
    return Str.trimEnd.apply(this, arguments);
}

// Cookie

export function getCookie() {
    return Cookie.getCookie.apply(this, arguments);
}

export function removeCookie() {
    return Cookie.removeCookie.apply(this, arguments);
}

export function setCookie() {
    return Cookie.setCookie.apply(this, arguments);
}

// URL

export function url() {
    return Router.url.apply(this, arguments);
}

export function toAbsolute() {
    return Router.toAbsolute.apply(this, arguments);
}

export function isExternal() {
    return Router.isExternal.apply(this, arguments);
}

export function asset() {
    return Router.asset.apply(this, arguments);
}

export function getParameterByName() {
    return Router.getParameterByName.apply(this, arguments);
}

export function getParameters() {
    return Router.getParameters.apply(this, arguments);
}

export function getHash() {
    return Router.getHash.apply(this, arguments);
}

export function addQueryParam() {
    return Router.addQueryParam.apply(this, arguments);
}

export function removeQueryParam() {
    return Router.removeQueryParam.apply(this, arguments);
}

export function removeHash() {
    return Router.removeHash.apply(this, arguments);
}

export function addHash() {
    return Router.addHash.apply(this, arguments);
}

export function parseDate() {
    return DateUtil.parseDate.apply(this, arguments);
}

export function isMobile() {
    return Mobile.isMobile.apply(this, arguments);
}

export function filterCollectionByLoginState() {
    return Entity.filterCollectionByLoginState.apply(this, arguments);
}

export function filterByLoginState() {
    return Entity.filterByLoginState.apply(this, arguments);
}

export function parseJwt(token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(
        window.atob(base64)
            .split('')
            .map(function (c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join('')
    );

    return JSON.parse(jsonPayload);
}
