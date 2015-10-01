silverstripe-mailchimp
=======================

MailChimp subscription form

Customizable mailchimp subscribtion form. Simply put the form into your template, and eventually
customize it through a .ss template file.
It could also be injected into a widget, or used inside a page through the MailChimpExtension extension.

## Maintainer Contact

Gabriele Brosulo [gabriele.brosulo (at) zirak (dot) it]

## Features

* Customizable form template
* Subscription widget form
* Static function callable from outside

*Warning!* Version 2.0 is not compatible with 1.* configuration. Page extension now is MailChimpExtension (no more MailChip)

## Requirements

* Silverstripe framework 3.1.*
* Silverstripe cms 3.1.*

## Suggestions

* silverstripe/widget
* zirak/widget-pages-extension

## Install

Install it through composer: 

```
	composer require zirak/silverstripe-mailchimp
```

### Using the form

From everywhere in your template you can render your form 

```HTML
<div id="mailchimp-form">
	$MailChimpSubscribeForm
</div>
```

You can eventually also override the default template, just add a MailChimpSubscribeForm.ss in themes/mytheme/template/Includes:

```HTML
<form $FormAttributes>

	$Fields.dataFieldByName(SecurityID)
	
	<h3> NEWSLETTER TRIBE </h3>
	<ul>
		<li>
			<div class="input-append newsLatterBox text-center">
				<% if $Fields.dataFieldByName(Email).Message %><span class="message $Fields.dataFieldByName(Email).MessageType">$Fields.dataFieldByName(Email).Message</span><% end_if %>
				$Fields.dataFieldByName(Email).addExtraClass(full).addExtraClass(text-center).setAttribute(placeholder, Email)
				
				<button class="btn  bg-gray" type="submit"> Registrati <i class="fa fa-long-arrow-right"> </i> </button>
			</div>
		</li>
	</ul>

</form>
```

### Using in a page

Extend the Page class (or whatever class you want to use) whit the MailChimp extension:

```YAML
---
Name: mailchimp-extensions
---
Page:
  extensions:
    - MailChimpExtension
```

Then render the $MailChimpSubscribeForm variable inside your template:

```
<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">$Content</div>
	</article>
		$Form
		$PageComments
		$MailChimpSubscribeForm
</div>
```

### Using inside a widget

Simply use the MailChimpSubscribe widget as usual [documentation here](https://github.com/silverstripe/silverstripe-widgets)

### Using inside a widget with widget-pages-extension

Define the widgetareas in your pages, like stated in [widget-pages-extension module](https://github.com/g4b0/silverstripe-widget-pages-extension)
After that define which widget are allowed for your pages, in particular MailChimpSubscribe.

For example your Page.php will become

```php
class Page extends SiteTree {

	private static $db = array(
	);
	private static $has_one = array(
			'SideBar' => 'WidgetArea'
	);
	private static $allowed_widgets = array(
			'MailChimpSubscribe'
	);

}
```

Run a /dev/build?flush=all and enjoy your widgets. Due to a known issue in widget-pages-extension you have to save each page you will need to put the widgets on.
For more information about how to use the widgets please see the [widget-pages-extension module documentations](https://github.com/g4b0/silverstripe-widget-pages-extension)

## Usage

* Configure your MailChimp APIKEY and ListID
* Enable the widget
* Play with the mailchimp.yml config file in your mysite folder, in conjunction with the MailChimp configurations. Follow a sample mailchimp.yml

```YAML
---
Name: mailchimp
---
MailChimpController:
  #apikey - see http://admin.mailchimp.com/account/api
  apikey: API-KEY
  # A List Id to run examples against. use lists() to view all
  # Also, login to MC account, go to List, then List Tools, and look for the List ID entry
  listid: LIST-ID
  redirect: true
  redirect_ok: 'reg-ok/'
  redirect_ko: 'reg-ko/'
  country: true
  topics: true
  topicsArr: ['Web development', 'Sysadmin', 'PHP', 'Javascript', 'HTML & CSS']
  otherTopic: true
```
