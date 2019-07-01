=== StaffList ===
Contributors: era404
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=464DEX6U6DL5N
Tags: staff, directory, faculty, personnel, phonebook, rubrik
Requires at least: 3.2.1
Tested up to: 5.1
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A super simplified staff directory tool

== Description ==

A very light-weight plugin, designed to easily create and manage a staff directory on your WordPress theme. Admins can manage staff records by importing (from CSV) or editing records individually. Record columns may be reordered (or hidden). Directory is sortable by column header, paginated, searchable, and customizable (by subset of records or number of records per page) using simple shortcode attributes.


**Simple, Straight-Forward**

The plugin is very light-weight, but robust. It allows for the individual management of staff directory records, or append/replace using a simple CSV (comma-separated variables) document.

* Update five standard fields for each staff record (last name, first name, department, email, phone number)
* Import from CSV or XLS allows administrators to keep their staff directory maintained, either by appending new records, or replacing the entire directory with an updated list
* Fields are not required (and may be left blank) for general department mailboxes or numbers
* Updates are performed on-the-fly, so no lengthy reloads are necessary
* Design is split into separate stylesheet for ease of theming
* No edit links or popups, just make your changes in-line
* Case-insensitive substring search, with highlighted matches on front end
* Uses jQuery/AJAX for page handling, sorting & searching without pageload (either on type, on enter, or both)
* Tipsy is incorporated for contact cards, to display additional information about each StaffList record
* StaffList can be placed into content editor using the shortcode [stafflist]
* Multiple StaffLists are supported in a single page and are customizable by shortcode attribute (e.g.: [stafflist subset="department:marketing"])
* Language is customizable for standard column labels and other messaging

**StaffList Public Demo**

A demo of this plugin can be found on the developer's site: 
[http://www.era404.com/info/stafflist-demo/](http://www.era404.com/info/stafflist-demo/ "StaffList (public demo on ERA404.com)")

== Installation ==

1. Install StaffList either via the WordPress.org plugin directory, or by uploading the files to your server (in the `/wp-content/plugins/` directory).
1. Activate the plugin.
1. Create a staff record (or as many as you need)
1. Insert the staff directory into your page using the shortcode **[stafflist]**, or customize with the following shortcode attributes.

**Shortcode Attributes**

* **[stafflist]** *include all records with default rows per page*
* **[stafflist rows=50]** *include all records with specified 50 rows per page*
* **[stafflist subset="department:marketing"]** *include only records that have "marketing" in the standard department column*
* **[stafflist subset="state:michigan"]** *include only records that have "michigan" in the non-standard state column*
* **[stafflist subset="building:a|b"]** *include only records that have "a" or "b" in the non-standard building column*
* **[stafflist on="enter"]** *perform the search when the enter key is pressed, instead of while typing (default)*


== Screenshots ==

1. The backend directory manager, for adding or modifying records individually
2. The backend directory manager, for importing a full directory list from CSV
3. The backend directory manager, for choosing and ordering your columns
4. The contact card displays additional information about each contact record
5. Example of a directory search
6. Customizing the front-end language (e.g.: Last Name --> Surname)

== Frequently Asked Questions ==

= Are there any new features planned? =
Yes. We plan to add a feature to use infinite scroll instead of page numbers.

= Can i propose a feature? =
If you wish. Sure.

== Changelog ==
= 2.6.3 =
* Clear search button added.

= 2.6.2 =
* Contact card too now respects the user-specified column order.

= 2.6.1 =
* Fixed export conflicts with adjacent plugins.

= 2.6.0 =
* Fixed javascript issue with some themes.

= 2.5.9 =
* Fallback gracefully to strtolower where mb_strtolower isn't available.
* This was producing fatal errors on servers without the multibyte string extension.

= 2.5.8 =
* Tested on WordPress 5.1; fixed jquery-ui-sortable issue;
* Expanded customization form for front-end messaging: "[#] Results", "No Results Found.", "Search Directory", and "Page".

= 2.5.6 =
* Tested on WordPress 5.0.3 and updated documentation;

= 2.5.5 =
* Added contact card in thead and aria-labelledby for search field, per request;

= 2.5.4 =
* Removed single record insert form (redundant);
* Added customization for standard column titles, per @metalflower88 (thank you).

= 2.5.2 =
* Cleaned up redundant [permitted] filetypes list;

= 2.5.1 =
* Changed sanitize on admin tools for searching foreign characters, per @pojeck (thank you).
* Tested with WordPress core 5.0.

= 2.5.0 =
* Added an option to toggle conforming imported data from variable encoding to UTF8 ("Clean");

= 2.4.8 =
* Added support for importing staff links (depicted as an icon) per request by @pojeck (thank you);
* Links can be used by import a spreadsheet containing a column called "url", "profile", or "link";
* This not treated as a "standard" column, like the others, as it will require flushing WP database to rebuild;

= 2.4.5 =
* Added a hyperlink to phone numbers (tel:xxx)

= 2.4.4 =
* Fixed the subset query to allow for spaces. For example: [stafflist subset="school:High School"];

= 2.4.3 =
* Added a text scrubber to attempt to conform imported text to UTF8 (clean);

= 2.4.2 =
* Made localized object keys more unique to prevent plugin conflicts;

= 2.4.1 =
* Added multiple word search;

= 2.4.0 =
* Increased max custom columns from 10 to 15 per request by @cetaceancousins; 
* Added export (to CSV/XLSx) of backend sort/search (or all) per request by @kaypohl;

= 2.3.7 =
* Search queries now parameterized and phone added to searched columns;

= 2.3.6 =
* Checks roles & capabilities on init;

= 2.3.5 =
* Reorganized admin interface to put full directory first; Collapsible instructions panel; Add an individual staff record form now includes non-standard columns;

= 2.3.2 =
* Fixed a bug that cleans and resets data-tables upon activation.

= 2.3.1 =
* Updated pager for administrators.

= 2.3.0 =
* StaffList now supports .xls/x import format, courtesy of PHPExcel Lib and recommendations by virgikins and gustavoo (thank you).

= 2.2.4 =
* Modified CSV interpretation to support broadest range of UTf8 characters, per request by user virgikins and gustavoo (thank you).

= 2.2.3 =
* Added shortcode to set search action on 'type', on 'enter', or on both (default), per request by user virgikins (thank you).

= 2.2.2 =
* Defined a new role/capability for StaffList Editors, per request by user valdemaras (thank you).

= 2.2.1 =
* Changed the INSERT from $wpdb->Insert to $wpdb->Query with prepared query to fix a bug brought up by blastiu17 (thank you).

= 2.2.0 =
* Introducing subsets! StaffList now gives users control over which records are included in each instance of the directory using simple shortcode attributes. Multiple StaffLists can also be added in a single WordPress page.

= 2.1.4 =
* Fixed an issue with sorting by custom columns; per pawlaz (thank you).

= 2.1.3 =
* Changed PHP4-style constructor to PHP7; per recommendation by NetzzJD (thank you).

= 2.1.2 =
* New admin feature to search the full directory; per recommendation by gustavoo (thank you).

= 2.1.1 =
* Replaced mysql_real_escape_string(); per recommendation by encweb (thank you).
* Added white-space: nowrap; per recommendation by digitlbill (thank you).

= 2.1 =
* Major release: This version allows admins to order columns by preference, and choose which will be visible in the StaffList directory (versus those in the contact card). All columns are searched, and there are now upto 10 custom columns in addition to the five standard columns. Please be advised that this version does require a rebuild of the database tables, and will clean out any directory data you currently have stored.

= 2.0.1 =
* Bug Fix: Updated ajax_object to use ajaxurl

= 2.0.0 =
* New release now supports CSV import to append or replace your staff directory
* More intuitive front-end sorting by First Name, Last Name, Department and Email Address
* Better responsive style for narrower content areas
* Added support for up to 9 custom columns (e.g.: Title, Building, Room) that are also searchable
* Contact card now has hover to show additional columns 

= 1.0.6 =
* Minor change to allow multiple stafflist instances per page (for responsive view, perhaps). Testified/Verified Compatibility with WordPress 4.4.1

= 1.0.5 =
* Testified/Verified Compatibility with WordPress 4.3

= 1.0.4 =
* Update to fix interference with paging styles from some themes.

= 1.0.3 =
* Testified/Verified Compatibility with WordPress 4.2.2

= 1.0.2 =
* Added donate link ;)

= 1.0.1 =
* Added new plugin icons.

= 1.0 =
* StaffList now in official release.

= 0.96 =
* Fixed issue with users pressing enter on a realtime search.

= 0.95 =
* Adjusted pager to allow for user config of # records/page.
* define('RECORDS_PER_PAGE', 25); //stafflist.php

= 0.94 =
* Improved styles.

= 0.93 =
* Improved styles.

= 0.92 =
* Added a banner image.

= 0.91 =
* Added a new screenshot to show the regex search results

= 0.9 =
* Plugin-out only in beta, currently. Standby for official release.