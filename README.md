# REDCap CSS Injector
Allows administrators to inject CSS into surveys and data entry forms.

[![DOI](https://zenodo.org/badge/141312467.svg)](https://zenodo.org/badge/latestdoi/141312467)

## Prerequisites
- REDCap >= 8.0.3

## Easy Installation
- Obtain this module from the Consortium [REDCap Repo](https://redcap.vanderbilt.edu/consortium/modules/index.php) from the Control Center.

## Manual Installation
- Clone this repo into `<redcap-root>/modules/redcap_css_injector_v<version_number>` .
- Go to **Control Center > Manage External Modules** and enable REDCap CSS Injector.

## Configuration
Access **Manage External Modules** section of your project, and then click on CSS Injector configuration button.

In the configuration form, you can either create a global style for your project or define multiple styles for different contexts. Each context is defined by choosing a list of forms/instruments and/or limiting the scope to surveys or data entries.

The configuration form also provides an enable/disable switch for each one of your styles. Make sure to enable your styles.

If more than one style is applied to the same page, the CSS rules are applied in the order of appearance in the configuration form.

![Configuration screen](img/config.png)

## Sample CSS

For CSS ideas you could try in CSS injector see the [sample CSS files](samples/). These files were tested against surveys in REDCap 8.7.0. Please verify they work as desired in your REDCap before using them in production. Each is dependent on REDCap's style names. As those style names can change over time, they probably won't hold value forever.

## Writing CSS for the CSS Injector

To learn how to craft your own CSS for use in REDCap CSS Injector, you'll need to use the developer tools for your web browser. Chrome supports the [Chrome DevTools](https://developers.google.com/web/tools/chrome-devtools/).  Firefox supports [Firefox Developer Tools](https://developer.mozilla.org/en-US/docs/Tools).  Using one of these tools you can access a REDCap page and inspect an element of the page you want to change. The dev tools allow you to see what CSS controls an element's appearance. You can change the values you think might give you the effect you want and review the result in real time on that page.  Once you get the result you like, copy that CSS, trim it down to the part you care about, and test it in CSS inspector.
