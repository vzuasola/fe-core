# Slider Placeholder

You can now add a placeholder image for slider while JS is not yet loaded. This is to avoid unstyled list of slider items while page is loading.

Add the following code to the slider template (banner-slider.html.twig), just before the slider item container (< div class="banner-slides">)

```html
<img src="{{ asset('images/slider_placeholder_1920_500.png') }}" class="slider-placeholder-image">
```
> Placeholder image should be the same dimension with the slider images.
> You can copy and edit the current placeholder image and adjust its dimension
> to match your slider dimension and use that as placeholder image
