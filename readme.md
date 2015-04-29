Simplex WCM
=========

[tech description here]

Status
----
This project is in development and is currently being tested. The final version has not been released yet. You are welcome
to install it and try it out. A working demo can be found here: link.

Features
----
shorten feature list, add link to documentation at bottom

####Content features

   * Intuitive and easy to use content management and publishing
   * Powerful content options to customize and differentiate your work
   * Transparent support for multimedia content
   * Automatic content enrichment, classification and semantic tagging
   * Per-post keywords and metadata specification
   * Twitter integration enables automatic tweeting when new content is added
   * Facebook integration in development
  
####Media features

   * Drag and drop media import supporting multifile uploads
   * Automatic video and image thumbnail generation
   * Metadata extraction for images using EXIF data
   * Metadata extraction for videos using ffmpeg
   * Automatic image watermarking
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
   * Two user groups for editors and administrators with different levels of access
   * Full user profile for each user
   * Support for public user registration
   * Secure password and credentials storage
   * Account activation and reactivation via email
  
Technical
----

Uses the following software packages to work:

   * Any SQL server supported by Doctrine 2 ORM for data storage
   * Doctrine 2 ORM for object relation mapping
   * Silex PHP as the base microframework, extended where necessary by Symfony 2 components
   * SwiftMailer for email operations
   * Twig for user interface templating
   * Assetic for user interface asset management
   * Imagine imaging library for image processing
   * Composer for dependency management and installation


Documentation
----

For Simplex documentation visit [http://www.envee.eu/simplex].

####Installation

  1. Clone or download the source files from the repository to your web server
  2. Run composer install in the directory to which the sources have been downloaded
  3. After Composer finishes the vendor installation it will install the Simplex application and prompt you to create the first user upon completion. If you don’t create the user, it will be generated automatically with preset values (*). 
  4. Depending upon the configuration of your web server, the Simplex application should be available at your-root/simplex-dir/admin


Development
----
Simplex is an open source project that welcomes quality contributions in form of code, suggestions, bug reports, translations, etc. There is a to-do wishlist and a planned feature roadmap - before contributing please consult the roadmap. 