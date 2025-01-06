# Basic Routing

This guide will help you get started developing with Web Composer.

### Basic Rules and Best Practices

Each route entry should contain (in most cases) the following:
* One Controller
* One SASS file
* One Script file
* One template file

First, register your route on your sites `app/config/routes.yml`

```yaml
routes:
    /:
        method: GET
        action: App\Demo\Controller\PageController:home

    /about:
        method: GET
        action: App\Demo\Controller\PageController:about
```

#### The Page Controller
* It should be using a `BaseSectionTrait` to group common Sections
* It should be `Async`
* It should only return data from other Sections
* It can have small logic for filtering or manipulating data
* It should return a Twig Response or a Rest Response

> Sections and Async will be explained in a different guide

```php
namespace App\MyProduct\Controller;

use App\BaseController;
use App\Async\Async;

use Slim\Exception\NotFoundException;

class PageController extends BaseController
{
    use BaseSectionTrait;

    /**
     *
     */
    public function home($request, $response)
    {
        // fetch data from other components
        $data = $this->getBaseData();

        $data['title'] = 'My Title';

        $data = Async::resolve($data);

        // page specific simple logic for controller
        if ($data['content']['disable']) {
            unset($data['content']);
        }

        return $this->view->render($response, '@site/home.html.twig', $data);
    }
```

#### The Page SASS Stylesheet
* It should only consume other SASS components
* It can have override styles but it should be a style for that specific page only

```sass
// Import components
@import "base/common";
@import "../../core/core/assets/sass/components/some-component";

// Page specific styles
myoverride-style {
    text-align: red;
}
```

#### The Page JavaScript
* It should always import a `base` Javascript file
* It should consume one SASS component
* It should only consume other Javascript components
* It can have small logic specific for that page

```javascript
// SCSS
require("./../sass/video.scss");

// Import components
import './base';
import * as utility from "Base/utility";
import VideoPlayer from "Base/media/video-player";

// Page specific script logic
utility.forEach(document.querySelectorAll('.video'), function (videoItem) {
    // some logic
});
```

#### The Page Template File
* It should require one SASS and one Javascript asset for the specific page
* It should only require other template components
* Other markup components should be split into separate twig components

```twig
{% extends '@site/page.html.twig' %}

{% block css %}
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
{% endblock %}

{% block main %}
    <div class="container mt-50">
        <!-- If I need components I just require them -->
        {{ include('@site/components/sample-video.html.twig') }}
        {{ include('@site/components/sample-video-modal.html.twig') }}
    </div>
{% endblock main %}

{% block script %}
    <script src="{{ asset('js/home.bundle.js') }}"></script>
{% endblock %}
```
