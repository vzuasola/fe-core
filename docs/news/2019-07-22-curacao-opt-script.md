# Curacao Optimization Script

Guide for the new implementation of Curacao.

The enabling of Curacao Partners Logo is still the same way in this [Curacao Partners Logo](docs/news/2018-01-17-curacao-partners-logo.md) tutorial. The only difference for the new implementation is by using the `Curacao Opt Script` which is a Marketing script in loading and handling the replacement of images.

On Drupal, configure `Curacao Settings` (admin/config/webcomposer/config/curacao):

**Enable checkbox:**
There was a newly added option for switching status. This checkbox enables to use the `Curacao Opt Script`, which is created to avoid slowness in loading of the site, we use a marketing script where it will be forced to execute during window.load only. 

**Curacao Optimization Marketing Script:**
```html
<script>(document.domain === 'www.dafabet.com' && app.settings.curacao_script !== '' && app.settings.enable_marketing_script === 1) ? window.applyMarketingScript("curacaoScript",function(){var e=document.getElementsByTagName("head")[0],a=document.createElement("script");a.type="text/javascript",a.src=app.settings.curacao_script,e.appendChild(a),a.onload=function(){var e=document.querySelector(".partners-logo-curacao");if(document.getElementById("apg-seal-link")&&e){var a=e.parentNode;"PICTURE"===a.tagName||"picture"===a.tagName?a.classList.add("hidden"):e.classList.add("hidden")}}}) : '';</script>
```

While when it's disabled, it will be functioning the usual way, which is the use of `curacao.js` to replace the logo being uploaded.