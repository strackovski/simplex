Simplex CMS
=========

Features
----

####Content features

   * Intuitive and easy to use content management and publishing
   * Powerful content options to customize and differentiate your work
   * Transparent support for multimedia content
   * Automatic content enrichment, classification and semantic tagging
   * Per-post keywords and metadata specification
  
####Media features

   * Drag and drop media import supporting multifile uploads
   * Automatic video and image thumbnail generation
   * Metadata extraction for images using EXIF data (if available)
   * Metadata extraction for videos using ffmpeg (if available)
   * Automatic image watermarking if watermark is uploaded
   * User defined image resampling quality
   * Media library resampling enabled on demand

####Page features

   * Bring structure to your web site with user-defined human friendly links
   * Use page queries to clearly define publishing criteria and rules for each page
   * *View templates*
   
####Templating and theming

   * Supports templates written in pure PHP and Twig templating engine
   * Theme templates recieve data from pages, no usage-pattern regarding display is enforced
   * Theme upload and selection supported in UI
   * Create your own theme by following very few very simple rules
   * Theme assets (styles, scripts) are compressed and minified for maximum performance
   
####Users and security

   * Multiuser support with very basic content change-tracking
   * Two user groups for editors and administrators with differentiating levels of access
   * Full user profile for each user
   * Support for public user registration
   * Secure password and credentials storage
   * Account activation and reactivation via email
   * Password reset support via email
  
####Technical

Uses the following software packages to work:
   * Any SQL server supported by Doctrine 2 ORM for data storage
   * Doctrine 2 ORM for object relation mapping
   * Silex PHP as the base microframework, extended where necessary by Symfony 2 components
   * SwiftMailer for email operations
   * Twig for user interface templating
   * Assetic for user interface asset management
   * Imagine imaging library for image processing
   * Composer for dependency management and installation

####Installation

At this moment there is no friendly browser installer available, so all installations must be completed via command line:
  1. Clone or download the source files from the repository to your web server
  2. Run composer install in the directory to which the sources have been downloaded
  3. After Composer finishes the vendor installation it will install the Simplex application and prompt you to create the first user upon completion. If you don’t create the user, it will be generated automatically with preset values (*). 
  4. Depending upon the configuration of your web server, the Simplex application should be available at your-root/simplex-dir/admin
  
How to use
----
### 1. General usage with a preset theme

Simplex aims to be intuitive for every web site publisher, from novice to experienced, and as such has no special manual. The general process of content publishing with Simplex consists of writing the content to be published, attaching any imported media items like images and videos and finally publishing the content by creating a page that has a user friendly URL link. No special usage patterns are enforced and everything else is optional.

### 2. Uploading a ready-made theme

To add an existing Simplex theme to your project, simply upload it in the Theme section under settings. After upload completes, you can switch to the new theme on the same screen.

### 3. Creating a web site from scratch

You can create your own unique theme for Simplex, providing you have basic knowledge of HTML and templating: a template is a text file containing data as variables that get evaluated once the template is displayed to the browser. A theme is a collection of common-style templates that together form a full web page. It is the theme, together with its templates, that defines the look and feel of your public facing web site.

Thanks to Twig support, there is no need to write a single line of pure PHP when creating your theme. The Twig manual has everything covered on templates, make sure to read it and use it as reference when creating your theme. http://twig.sensiolabs.org/doc/templates.html - Twig for Designers

####Template structure

In this chapter it is assumed that you have a ready-made HTML template that you’d like to convert into a Simplex theme. Every Simplex theme is structured on the filesystem in the following way:

   * ThemeName

      * assets/

         * fonts/
         * images/
         * scripts/
         * styles/

      * views/
      * masters/
      * theme.xml
      * theme.json


The assets/ folder is pretty much self-explanatory: you put your fonts in the fonts/ dir, images in images/, etc. 
The views folder is where your view-templates go: whatever template you put in views dir will be available as a template when creating a page. 

The masters folder is for layout master templates that the templates in views folder can extend.  
Files theme.xml and theme.json hold some basic theme information: only one version is needed, not both (pick json or xml). 

####Masters and slaves, er, views

There are two types of templates in Simplex: masters and views (master-template and view-template). Masters define the layout of a page acting as a frame into which view-templates are joined at run time by use of template inheritance. To learn more about this please consult Twig documentation, however a simple example is presented bellow...


####Including assets in masters

The style and script assets for your theme are compresed and minified to the web folder for performance reasons, so when including these assets in the master templates, you should include them from the web folder, not the theme assets folder, at least for production.


####Recieving and displaying data

The data for each page is collected and channeled to the displayed template automatically by Simplex. In every template you have access to this data array in the “data” variable. At the very least the data variable contains:


| Key      | Description                       | Type     |
|----------|-----------------------------------|----------|
| title    | Page title as defined in pages    | string   |
| slug     | The generated page slug           | string   |
| modified | Date and time of last page change | DateTime |
| options  | Page options as defined in pages  | array    |
| query    | Results of PageQuery (optional)   | object/array         |

When you create a page through the Simplex web interface you have the ability to define a data query for that page. This query retrieves additional data such as posts or media items and sends them to the template along with the rest of the data. If a query is defined for a page, than the above data variable also contains the results for the query, available to you under key “query”.


####Handling object data types

Special Simplex types are described in the API documentation, here only the most common are listed:


*Post type*

| Property   | Type                   | Description    |
|------------|------------------------|----------------|
| title      | string                 | Title          |
| subtitle   | string                 | Subtitle       |
| body       | string                 | Body text      |
| author     | Entity\User            | Post author    |
| editor     | Entity\User            | Last editor    |
| mediaItems | Collection [MediaItem] | Attached media |
| tags       | Collection [Tag]       | Post tags      |
| metadata   | Entity\Metadata        | Post metadata  |
| createdAt  | DateTime               | Date added     |
| updatedAt  | DateTime               |                |

*MediaItem*

| Property     | Type            | Description                 |
|--------------|-----------------|-----------------------------|
| path         | string          | Relative path to media file |
| absolutePath | string          | Absolute path to media file |
| metadata     | Entity\Metadata | Media metadata              |

*Page*

| Property | Type                   | Description        |
|----------|------------------------|--------------------|
| title    | string                 | Title              |
| slug     | string                 | Page URL slug      |
| queries  | Collection [PageQuery] | Page data queries  |
| inMenu   | boolean                | Include in menu    |
| view     | string                 | Page view-template |

Development
----
Simplex is an open source project that welcomes quality contributions in form of code, suggestions, bug reports, translations, etc. There is a to-do wishlist and a planned feature roadmap - before contributing please consult the roadmap. 