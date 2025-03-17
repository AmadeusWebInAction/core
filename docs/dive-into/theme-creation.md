## Some Background

Core v7 onwards uses a **[theme.php](https://github.com/AmadeusWebInAction/core/blob/main/framework/theme.php)** which expects a single html file with placeholders and variable substitution using ## - like ##menu## etc. We no longer need php files, though a rich-page using html only is in the works.

---

## Sub-theme

We use [canvastemplate](https://canvastemplate.com/) extensively and, to code a sub-theme as we call it, these are the steps.

* decide what the internal name (subtheme) is going to be (say flip)
* copy the file from `Canvas 7 Files` and rename it to flip(original-name).html
* run it in the browser - images etc should be prefixed with https://canvastemplate.com/ so the design loads assets but not from the theme site.
* since everything works by relative path, the required css / js etc assets can be copied over into the theme (in the same folder structure).
* for instance, if you do a diff of `business.html` (amadeus aware template) and `business(demo-business).html` (original with canvas based assets) you can better understand this.
* Earlier, we would include in the theme zip, all the images, and did not have a **sub-theme** concept so there was lots of duplication of files when using `canva`.

---

## Making the template Amadeus Aware

Once the canvas original is ready, we will do the following.

* Copy the file as just `[sub-theme].html`.

---

## HEADER PART

* In the head tag,
	* replace author / description with `##seo##`
	* theme assets need to load from the theme-url, so add a `##theme##` prefix.
	* document title should become `##head-includes##`
* In the body
	* body shold have a class attibute, and if there is any existing class, a space before `##body-classes##`
	* header logo's link with inner image should be replaced with `##logo##`
	* after the `<nav class="primary-menu">` there should be an optional ##menu## (it was made optional for go - block-content), replacing the entire `<ul>`.
	* a ##search-url## is foreseen but not yet achieved.
	* if the theme had a slider, replace it with ##optional-slider##. It is the job of enrichThemeVars to decide based on the page whether to set it or not. if not set, it retains the original '' (empty space) and a 'no-slider' class will be addded to the body (header background color is set in this case).
	* class="content" should be added to the main section/div (usually #content)
	* ##content## where the actual content should be rendered (between the theme parts header + footer) - the parts are **split** by the content placeholder. Sometimes this is inside a `.content-wrap` div
	
	
---	

## FOOTER PART

* for now, footer-widgets is mandatory (since we didnt want to duplicate the definition in each sub-theme), so ##footer-widgets## in it, and a **footer-widgets.html
* remember the `##theme##` for remaining assets


---

## Publishing

* only the template html is checked into git, the original html and any new assets are added to the zip file (new version is given) and other developers // clients intimated to update the [themes repository](https://github.com/AmadeusWebInAction/themes).

