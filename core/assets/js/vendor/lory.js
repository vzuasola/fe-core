!function(e,t){if("object"==typeof exports&&"object"==typeof module)module.exports=t();else if("function"==typeof define&&define.amd)define([],t);else{var n=t();for(var i in n)("object"==typeof exports?exports:e)[i]=n[i]}}("undefined"!=typeof self?self:this,function(){return function(e){function t(i){if(n[i])return n[i].exports;var o=n[i]={i:i,l:!1,exports:{}};return e[i].call(o.exports,o,o.exports,t),o.l=!0,o.exports}var n={};return t.m=e,t.c=n,t.d=function(e,n,i){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:i})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=7)}([function(e,t){var n;n=function(){return this}();try{n=n||Function("return this")()||(0,eval)("this")}catch(e){"object"==typeof window&&(n=window)}e.exports=n},function(e,t,n){"use strict";function i(e){return e&&e.__esModule?e:{default:e}}function o(e,t){function n(e,t){var n=D,i=n.classNameActiveSlide;e.forEach(function(e,t){e.classList.contains(i)&&e.classList.remove(i)}),e[t].classList.add(i)}function i(e){var t=D,n=t.infinite,i=e.slice(0,n),o=e.slice(e.length-n,e.length);return i.forEach(function(e){var t=e.cloneNode(!0);P.appendChild(t)}),o.reverse().forEach(function(e){var t=e.cloneNode(!0);P.insertBefore(t,P.firstChild)}),P.addEventListener(T.transitionEnd,L),m.call(P.children)}function o(t,n,i){(0,u.default)(e,t+".lory."+n,i)}function s(e,t,n){var i=P&&P.style;i&&(i[T.transition+"TimingFunction"]=n,i[T.transition+"Duration"]=t+"ms",T.hasTranslate3d?i[T.transform]="translate3d("+e+"px, 0, 0)":i[T.transform]="translate("+e+"px, 0)")}function d(e,t){var i=D,r=i.slideSpeed,a=i.slidesToScroll,d=i.infinite,l=i.rewind,c=i.rewindSpeed,u=i.ease,f=i.classNameActiveSlide,v=r,p=t?z+1:z-1,h=Math.round(M-_);o("before","slide",{index:z,nextSlide:p}),B&&B.classList.remove("disabled"),k&&k.classList.remove("disabled"),"number"!=typeof e&&(e=t?z+a:z-a),e=Math.min(Math.max(e,0),S.length-1),d&&void 0===t&&(e+=d);var b=Math.min(Math.max(-1*S[e].offsetLeft,-1*h),0);l&&Math.abs(j.x)===h&&t&&(b=0,e=0,v=c),s(b,v,u),j.x=b,S[e].offsetLeft<=h&&(z=e),!d||e!==S.length-d&&0!==e||(t&&(z=d),t||(z=S.length-2*d),j.x=-1*S[z].offsetLeft,A=function(){s(-1*S[z].offsetLeft,0,void 0)}),f&&n(m.call(S),z),B&&!d&&0===e&&B.classList.add("disabled"),!k||d||l||e+1!==S.length||k.classList.add("disabled"),o("after","slide",{currentSlide:z})}function c(){o("before","init"),T=(0,a.default)(),D=r({},v.default,t);var s=D,d=s.classNameFrame,l=s.classNameSlideContainer,c=s.classNamePrevCtrl,u=s.classNameNextCtrl,p=s.enableMouseEvents,h=s.classNameActiveSlide,y=s.initialIndex;z=y,O=e.getElementsByClassName(d)[0],P=O.getElementsByClassName(l)[0],B=e.getElementsByClassName(c)[0],k=e.getElementsByClassName(u)[0],j={x:P.offsetLeft,y:P.offsetTop},D.infinite?S=i(m.call(P.children)):(S=m.call(P.children),B&&B.classList.add("disabled"),k&&1===S.length&&!D.rewind&&k.classList.add("disabled")),f(),h&&n(S,z),B&&k&&(B.addEventListener("click",b),k.addEventListener("click",E)),O.addEventListener("touchstart",x,R),p&&(O.addEventListener("mousedown",x),O.addEventListener("click",C)),D.window.addEventListener("resize",N),o("after","init")}function f(){var e=D,t=e.infinite,i=e.ease,o=e.rewindSpeed,r=e.rewindOnResize,a=e.classNameActiveSlide,d=e.initialIndex;M=P.getBoundingClientRect().width||P.offsetWidth,_=O.getBoundingClientRect().width||O.offsetWidth,_===M&&(M=S.reduce(function(e,t){return e+t.getBoundingClientRect().width||t.offsetWidth},0)),r?z=d:(i=null,o=0),t?(s(-1*S[z+t].offsetLeft,0,null),z+=t,j.x=-1*S[z].offsetLeft):(s(-1*S[z].offsetLeft,o,i),j.x=-1*S[z].offsetLeft),a&&n(m.call(S),z)}function p(e){d(e)}function h(){return z-D.infinite||0}function b(){d(!1,!1)}function E(){d(!1,!0)}function y(){o("before","destroy"),O.removeEventListener(T.transitionEnd,L),O.removeEventListener("touchstart",x,R),O.removeEventListener("touchmove",w,R),O.removeEventListener("touchend",g),O.removeEventListener("mousemove",w),O.removeEventListener("mousedown",x),O.removeEventListener("mouseup",g),O.removeEventListener("mouseleave",g),O.removeEventListener("click",C),D.window.removeEventListener("resize",N),B&&B.removeEventListener("click",b),k&&k.removeEventListener("click",E),D.infinite&&Array.apply(null,Array(D.infinite)).forEach(function(){P.removeChild(P.firstChild),P.removeChild(P.lastChild)}),o("after","destroy")}function L(){A&&(A(),A=void 0)}function x(e){var t=D,n=t.enableMouseEvents,i=e.touches?e.touches[0]:e;n&&(O.addEventListener("mousemove",w),O.addEventListener("mouseup",g),O.addEventListener("mouseleave",g)),O.addEventListener("touchmove",w,R),O.addEventListener("touchend",g);var r=i.pageX,s=i.pageY;F={x:r,y:s,time:Date.now()},W=void 0,I={},o("on","touchstart",{event:e})}function w(e){var t=e.touches?e.touches[0]:e,n=t.pageX,i=t.pageY;I={x:n-F.x,y:i-F.y},void 0===W&&(W=!!(W||Math.abs(I.x)<Math.abs(I.y))),!W&&F&&(e.preventDefault(),s(j.x+I.x,0,null)),o("on","touchmove",{event:e})}function g(e){var t=F?Date.now()-F.time:void 0,n=Number(t)<300&&Math.abs(I.x)>25||Math.abs(I.x)>_/3,i=!z&&I.x>0||z===S.length-1&&I.x<0,r=I.x<0;W||(n&&!i?d(!1,r):s(j.x,D.snapBackSpeed)),F=void 0,O.removeEventListener("touchmove",w),O.removeEventListener("touchend",g),O.removeEventListener("mousemove",w),O.removeEventListener("mouseup",g),O.removeEventListener("mouseleave",g),o("on","touchend",{event:e})}function C(e){I.x&&e.preventDefault()}function N(e){f(),o("on","resize",{event:e})}var j=void 0,M=void 0,_=void 0,S=void 0,O=void 0,P=void 0,B=void 0,k=void 0,T=void 0,A=void 0,z=0,D={},R=!!(0,l.default)()&&{passive:!0};"undefined"!=typeof jQuery&&e instanceof jQuery&&(e=e[0]);var F=void 0,I=void 0,W=void 0;return c(),{setup:c,reset:f,slideTo:p,returnIndex:h,prev:b,next:E,destroy:y}}Object.defineProperty(t,"__esModule",{value:!0});var r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var i in n)Object.prototype.hasOwnProperty.call(n,i)&&(e[i]=n[i])}return e};t.lory=o;var s=n(2),a=i(s),d=n(3),l=i(d),c=n(4),u=i(c),f=n(6),v=i(f),m=Array.prototype.slice},function(e,t,n){"use strict";(function(e){function n(){var t=void 0,n=void 0,i=void 0,o=void 0;return function(){var r=document.createElement("_"),s=r.style,a=void 0;""===s[a="webkitTransition"]&&(i="webkitTransitionEnd",n=a),""===s[a="transition"]&&(i="transitionend",n=a),""===s[a="webkitTransform"]&&(t=a),""===s[a="msTransform"]&&(t=a),""===s[a="transform"]&&(t=a),document.body.insertBefore(r,null),s[t]="translate3d(0, 0, 0)",o=!!e.getComputedStyle(r).getPropertyValue(t),document.body.removeChild(r)}(),{transform:t,transition:n,transitionEnd:i,hasTranslate3d:o}}Object.defineProperty(t,"__esModule",{value:!0}),t.default=n}).call(t,n(0))},function(e,t,n){"use strict";function i(){var e=!1;try{var t=Object.defineProperty({},"passive",{get:function(){e=!0}});window.addEventListener("testPassive",null,t),window.removeEventListener("testPassive",null,t)}catch(e){}return e}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i},function(e,t,n){"use strict";function i(e,t,n){var i=new r.default(t,{bubbles:!0,cancelable:!0,detail:n});e.dispatchEvent(i)}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i;var o=n(5),r=function(e){return e&&e.__esModule?e:{default:e}}(o)},function(e,t,n){(function(t){var n=t.CustomEvent;e.exports=function(){try{var e=new n("cat",{detail:{foo:"bar"}});return"cat"===e.type&&"bar"===e.detail.foo}catch(e){}return!1}()?n:"undefined"!=typeof document&&"function"==typeof document.createEvent?function(e,t){var n=document.createEvent("CustomEvent");return t?n.initCustomEvent(e,t.bubbles,t.cancelable,t.detail):n.initCustomEvent(e,!1,!1,void 0),n}:function(e,t){var n=document.createEventObject();return n.type=e,t?(n.bubbles=Boolean(t.bubbles),n.cancelable=Boolean(t.cancelable),n.detail=t.detail):(n.bubbles=!1,n.cancelable=!1,n.detail=void 0),n}}).call(t,n(0))},function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default={slidesToScroll:1,slideSpeed:300,rewindSpeed:600,snapBackSpeed:200,ease:"ease",rewind:!1,infinite:!1,initialIndex:0,classNameFrame:"js_frame",classNameSlideContainer:"js_slides",classNamePrevCtrl:"js_prev",classNameNextCtrl:"js_next",classNameActiveSlide:"active",enableMouseEvents:!1,window:window,rewindOnResize:!0}},function(e,t,n){e.exports=n(1)}])});
//# sourceMappingURL=lory.min.js.map