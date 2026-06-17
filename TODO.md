TODO
----

Packages
--------

### Upgrade following packages:

* chartjs (v2 -> v4)
* font-awesome (v4 -> v6)
  * ore replace with https://icons.getbootstrap.com/#install


### Other packages that are not-up-to-date, hence need upgrades:

* blueimp-file-upload ( blueimp/jquery-file-upload )
* hover (https://github.com/IanLunn/Hover/)
* libre-franklin (https://fontsource.org/fonts/libre-franklin + https://www.npmjs.com/package/@fontsource/libre-franklin/)
* phpass (https://www.openwall.com/phpass/ + https://github.com/openwall/phpass/)
  * Straight-forward upgrade to 0.5.4 breaks the login...
  * Alternative: switch to bordoni/phpass
* scrollreveal (https://github.com/jlmakes/scrollreveal/)


### Remove the need of patched external packages

* Find another way to use fontawesome-webfont with MPDF instead of patching MPDF


### Remove the external packages from the repository (use composer instead)

* Requires finding alternatives to patching packages (e.g. MPDF)


### Bundle Leaflet.js with the project so that it does not need to be loaded from CDN


### Stop loading JS files that the user does not need

* Load Leaflet.js from CDN only if the map was activated
* Load HCaptcha from CDN only if HCaptcha was activated


### Make the event- and user-import more user-friendly

* Show a nice dialog while importing instead of a text-only-page
* On error, try to inform the user what is wrong, e.g.:
  * wrongly formatted dates
  * wrong file-encoding (must be UTF-8 without BOM)


Add new features
----------------
* Add the hCaptcha on the login-page and on the contact-form
* Add the possibility to disable the hCaptcha selectively on some pages without the need to disable it globally
* On the registration page, let the user choose the category (e.g. "Ehepartner" or "Verbindungsfreund"), so more confusing cases will be clearer
  * The default should be "Philister"
* Allow registration for a "mailinglist"-only-user (without any user-login-possibility)
  * The purpose is to allow interested persons to be kept in the email-loop
* Automate the user- and malilinglist-registrations more
  * Instead of the now copy&pasting data from registration-emails, already create the user in the DB, but in a status "inactive-confirmation-needed"
    * A cron-job that would auto-delete such users, e.g. after 28 days, would be needed to not let such inactive users accumulate
  * For mailinglists, optionally allow complete automation - but only, if the email address has been automatically confirmed
* Add 2FA and/or passkeys
* Keycloak support from https://github.com/Chreuseo/vcms-keycloak (just maybe, to keep the forks synchronized)
* Member-map from https://github.com/Chreuseo/vcms-keycloak - or at least use it as base
* Nginx-support from https://github.com/Chreuseo/vcms-keycloak/commit/18985e3e3e32e0ae826de8b040f39414873d82cb


Known Bugs
--------
* hCaptcha cannot currently be disabled and a valid hCaptcha configuration is required for user-registration and password-reset to work
* The menu layout looks not ok on screen-width between 992px and 1199px, as the menu-items are split to several lines -> the mobile-layout with the hamburger menu should be extended to work with resolutions up to 1199px instead of 991px now (this is a side-effect of the Bootstrap-upgrade)
* On desktop, while scrolling down a page, the logo at the middle in the top does not fall down to the main-menu-level anymore but stays at top of the main-menu (this is a side-effect of the Bootstrap-upgrade)
* There are too many aria-hidden=true attributes, that likely break accessibility of the page. A full review of them is necessary


Fixed Bugs
----------
* Browser-Back-Button breaking the page directly after a POST
  * fixed with https://github.com/adrianer/vcms/commit/769c6c8272a0ee09fc5b93190b208c1c720cd842
* Browser-Refresh-Button creating new login-sessions sometimes
  * fixed with https://github.com/adrianer/vcms/commit/d3a24d9a377fbf0301c399c193047ce12341bd68
* Login Session very short - was longer before
  * fixed with https://github.com/adrianer/vcms/commit/d3a24d9a377fbf0301c399c193047ce12341bd68
* Navigation between photos with the keyboard arrows didn't work
  * fixed with https://github.com/adrianer/vcms/commit/432522a5874e0cc3f2c4108ce91bd41059fdd9c9
