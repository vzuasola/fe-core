// minifill.js | MIT | dnp_theme
(function(){

  // all repeated strings get a single reference
  // document | window | element + corrections
  var Doc = 'Document', doc = document, DOCUMENT = this[Doc] || this.HTMLDocument, // IE8
    WIN = 'Window', win = window, WINDOW =  this.constructor || this[WIN] || Window, // old Safari
    HTMLELEMENT = 'HTMLElement', documentElement = 'documentElement', ELEMENT = Element,

    // classList related
    className = 'className', add = 'add', classList = 'classList', remove = 'remove', contains = 'contains',
    
    // object | array related
    prototype = 'prototype', indexOf = 'indexOf', length = 'length',

    // performance
    now = 'now', performance = 'performance',

    // getComputedStyle
    getComputedStyle = 'getComputedStyle', currentStyle = 'currentStyle', fontSize = 'fontSize',

    // event related
    EVENT = 'Event', CustomEvent = 'CustomEvent', IE8EVENTS = '_events', 
    etype = 'type', target = 'target', currentTarget = 'currentTarget', relatedTarget = 'relatedTarget',
    cancelable = 'cancelable', bubbles = 'bubbles', cancelBubble = 'cancelBubble', cancelImmediate = 'cancelImmediate', detail = 'detail',
    addEventListener = 'addEventListener', removeEventListener = 'removeEventListener', dispatchEvent = 'dispatchEvent';

    
  // Element
  if (!win[HTMLELEMENT]) { win[HTMLELEMENT] = win[ELEMENT]; }

  // Array[prototype][indexOf]
  if (!Array[prototype][indexOf]) {
    Array[prototype][indexOf] = function(searchElement) {
      if (this === undefined || this === null) {
        throw new TypeError(this + ' is not an object');
      }
    
      var arraylike = this instanceof String ? this.split('') : this,
        lengthValue = Math.max(Math.min(arraylike[length], 9007199254740991), 0) || 0,
        index = Number(arguments[1]) || 0;
    
      index = (index < 0 ? Math.max(lengthValue + index, 0) : index) - 1;
    
      while (++index < lengthValue) {
        if (index in arraylike && arraylike[index] === searchElement) {
          return index;
        }
      }
    
      return -1;
    };
  }

  // Date[now]
  if(!Date[now]){ Date[now] = function() { return new Date().getTime(); }; }

  // performance[now]
  (function(){
    if (performance in win == false) {  win[performance] = {}; }
    
    if (now in win[performance] == false){  
      var nowOffset = Date[now]();
      
      window[performance][now] = function(){
        return Date[now]() - nowOffset;
      }
    }
  })();


  // getComputedStyle
  if (!(getComputedStyle in win)) {
    function getComputedStylePixel(element, property, fontSizeValue) {
      
      // Internet Explorer sometimes struggles to read currentStyle until the element's document is accessed.
      var value = element.document && element[currentStyle][property].match(/([\d\.]+)(%|cm|em|in|mm|pc|pt|)/) || [0, 0, ''],
        size = value[1],
        suffix = value[2],
        rootSize;
  
      fontSizeValue = !fontSizeValue ? fontSizeValue : /%|em/.test(suffix) && element.parentElement ? getComputedStylePixel(element.parentElement, 'fontSize', null) : 16;
      rootSize = property == 'fontSize' ? fontSizeValue : /width/i.test(property) ? element.clientWidth : element.clientHeight;
  
      return suffix == '%' ? size / 100 * rootSize :
        suffix == 'cm' ? size * 0.3937 * 96 :
        suffix == 'em' ? size * fontSizeValue :
        suffix == 'in' ? size * 96 :
        suffix == 'mm' ? size * 0.3937 * 96 / 10 :
        suffix == 'pc' ? size * 12 * 96 / 72 :
        suffix == 'pt' ? size * 96 / 72 :
        size;
    }
  
    function setShortStyleProperty(style, property) {
      var  borderSuffix = property == 'border' ? 'Width' : '',
        t = property + 'Top' + borderSuffix,
        r = property + 'Right' + borderSuffix,
        b = property + 'Bottom' + borderSuffix,
        l = property + 'Left' + borderSuffix;
  
      style[property] = (style[t] == style[r] && style[t] == style[b] && style[t] == style[l] ? [ style[t] ] :
              style[t] == style[b] && style[l] == style[r] ? [ style[t], style[r] ] :
              style[l] == style[r] ? [ style[t], style[r], style[b] ] :
              [ style[t], style[r], style[b], style[l] ]).join(' ');
    }
  
    // <CSSStyleDeclaration>
    function CSSStyleDeclaration(element) {
      var style = this,
      currentStyleValue = element[currentStyle],
      fontSizeValue = getComputedStylePixel(element, fontSize),
      unCamelCase = function (match) {
        return '-' + match.toLowerCase();
      },
      property;
  
      for (property in currentStyleValue) {
        Array.prototype.push.call(style, property == 'styleFloat' ? 'float' : property.replace(/[A-Z]/, unCamelCase));
  
        if (property == 'width') {
          style[property] = element.offsetWidth + 'px';
        } else if (property == 'height') {
          style[property] = element.offsetHeight + 'px';
        } else if (property == 'styleFloat') {
          style.float = currentStyleValue[property];
        } else if (/margin.|padding.|border.+W/.test(property) && style[property] != 'auto') {
          style[property] = Math.round(getComputedStylePixel(element, property, fontSizeValue)) + 'px';
        } else if (/^outline/.test(property)) {
          // errors on checking outline
          try {
            style[property] = currentStyleValue[property];
          } catch (error) {
            style.outlineColor = currentStyleValue.color;
            style.outlineStyle = style.outlineStyle || 'none';
            style.outlineWidth = style.outlineWidth || '0px';
            style.outline = [style.outlineColor, style.outlineWidth, style.outlineStyle].join(' ');
          }
        } else {
          style[property] = currentStyleValue[property];
        }
      }
  
      setShortStyleProperty(style, 'margin');
      setShortStyleProperty(style, 'padding');
      setShortStyleProperty(style, 'border');
  
      style[fontSize] = Math.round(fontSizeValue) + 'px';    
    }
    
    CSSStyleDeclaration[prototype] = {
      constructor: CSSStyleDeclaration,
      // <CSSStyleDeclaration>.getPropertyPriority
      getPropertyPriority: function () {
        throw new Error('DOM Exception 9');
      },
      // <CSSStyleDeclaration>.getPropertyValue
      getPropertyValue: function (property) {
        return this[property.replace(/-\w/g, function (match) {
          return match[1].toUpperCase();
        })];
      },
      // <CSSStyleDeclaration>.item
      item: function (index) {
        return this[index];
      },
      // <CSSStyleDeclaration>.removeProperty
      removeProperty: function () {
        throw new Error('DOM Exception 7');
      },
      // <CSSStyleDeclaration>.setProperty
      setProperty: function () {
        throw new Error('DOM Exception 7');
      },
      // <CSSStyleDeclaration>.getPropertyCSSValue
      getPropertyCSSValue: function () {
        throw new Error('DOM Exception 9');
      }
    };    
  
    // <Global>.getComputedStyle
    win[getComputedStyle] = function(element) {
      return new CSSStyleDeclaration(element);
    };
  }  

  // Element.prototype.classList by thednp
  if( !(classList in ELEMENT[prototype]) ) {
    var ClassLIST = function(elem){
      var classArr = elem[className].replace(/^\s+|\s+$/g,'').split(/\s+/) || [];

          // methods
          hasClass = this[contains] = function(classNAME){
            return classArr[indexOf](classNAME) > -1;
          },
          addClass = this[add] = function(classNAME){
            if (!hasClass(classNAME)) {
              classArr.push(classNAME);
              elem[className] = classArr.join(' ');
            }
          },
          removeClass = this[remove] = function(classNAME){
            if (hasClass(classNAME)) {
              classArr.splice(classArr[indexOf](classNAME),1);
              elem[className] = classArr.join(' '); 
            }
          },
          toggleClass = this.toggle = function(classNAME){
            if ( hasClass(classNAME) ) { removeClass(classNAME); } 
            else { addClass(classNAME); } 
          };
    }
    Object.defineProperty(ELEMENT[prototype], classList, { get: function () { return new ClassLIST(this); } });
  }

  // Event
  if (!win[EVENT]||!WINDOW[prototype][EVENT]) {
    win[EVENT] = WINDOW[prototype][EVENT] = DOCUMENT[prototype][EVENT] = ELEMENT[prototype][EVENT] = function(type, eventInitDict) {
      if (!type) { throw new Error('Not enough arguments'); }
      var event, 
        bubblesValue = eventInitDict && eventInitDict[bubbles] !== undefined ? eventInitDict[bubbles] : false,
        cancelableValue = eventInitDict && eventInitDict[cancelable] !== undefined ? eventInitDict[cancelable] : false;
      if ( 'createEvent' in doc ) {
        event = doc.createEvent(EVENT);      
        event.initEvent(type, bubblesValue, cancelableValue);
      } else {
        event = doc.createEventObject();
        event[etype] = type;
        event[bubbles] = bubblesValue;
        event[cancelable] = cancelableValue;
      }
      return event;
    };
  }

  // CustomEvent
  if (!(CustomEvent in win) || !(CustomEvent in WINDOW[prototype])) {
    win[CustomEvent] = WINDOW[prototype][CustomEvent] = DOCUMENT[prototype][CustomEvent] = Element[prototype][CustomEvent] = function(type, eventInitDict) {
      if (!type) {
        throw Error('CustomEvent TypeError: An event name must be provided.');
      }
      var event = new Event(type, eventInitDict);
      event[detail] = eventInitDict && eventInitDict[detail] || null;
      return event;
    };
  }
}());
