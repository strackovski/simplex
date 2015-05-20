Simplex WCM
=========

This is the core development version of Simplex web content management system, built with PHP 5. Use repository's tags to get working and tested releases. See documentation section for more information about installation and usage.

Features
----
   
Click the issues tab to see features currently under development.
   
####Content features

   * Intuitive content management and publishing
   * Powerful content publishing options
   * Transparent support for multimedia content
   * Automatic content enrichment and classification
   * Per-content keywords and metadata specification
  
####Media features

   * Drag and drop media import
   * Automatic media thumbnail generation
   * Automatic media metadata extraction
   * Automatic image watermarking if enabled
   * User defined image sampling quality

####Page features

   * Structure defined with user-defined SEO-friendly URLs
   * Page Queries define dynamic data to display on pages
   * Static content can be included in pages directly
   * Page-specific metadata supported for every page
   
####Templating and theming

   * Supports templates written in pure PHP and Twig
   * Templates receive data as defined in pages
   * Creating your own templates is as easy as HTML
   * Assetic is available for theme asset management
   
####Users and security

   * User groups for editors and administrators
   * Full user profile for each user
   * Support for public user registration
   * Secure password and credentials storage
   * Secure account activation and reactivation procedure
  
#### Next version
   
   Features planned for next release:
   
   * OpenCalais upgrade
   * Page layout manager and builder
   * Support for multi-conditional page queries
   * User group manager to define group access by modules
   * Comments and content rating system
   * Integrate Gearman job server for time-intensive processes   
   
  
Built with
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
For complete Simplex documentation visit [the docs page](http://www.envee.eu/projects/simplex/docs]).

####Installation

  1. Clone or download the source files from the repository to your web server
  2. Run composer install in the directory to which the sources have been downloaded
  3. After Composer finishes the vendor installation it will install the Simplex application and prompt you to create the first user upon completion. 
  4. Depending upon the configuration of your web server, the Simplex application should be available at your-root/admin/dashboard (see web/htacess.dist if you need it)
  5. web/uploads and var/logs directories should have appropriate permissions and owner settings to enable file uploads and logging (required)
  
Development
----
Simplex is an open source project that welcomes quality contributions in form of code, suggestions, bug reports, translations, etc.

#### Active developers
* [Vladimir Stračkovski](https://github.com/strackovski/)
* [Neja Dolinar](https://github.com/ndolinar/) (themes, frontend, UI)

License
----
This software is licensed under the Apache 2 license. To obtain a full copy of the license please [click here](http://www.apache.org/licenses/LICENSE-2.0). Also see the attached license.md for additional information.