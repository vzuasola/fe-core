# Recommended Marketing Script Format

When creating marketing script entries on the CMS, it is recommended to follow this format.


**Advantages**
* Allows script to load on _onDocumentLoad_ to prevent blocking operations that slows down the site
* Allows access to the utility library

```javascript
window.applyMarketingScript('sample.event', function (utility) {
    var cookie = utility.getCookie('sample.cookie');
});
```
