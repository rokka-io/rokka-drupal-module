# Rokka: Drupal integration Module

This module integrates [Rokka.io](https://rokka.io) with Drupal: after setting up your credentials the module allows to:

 - Automatically upload images from fields to Rokka by using the `rokka://` stream wrapper
 - Synchronize Drupal's Image Styles to Rokka's ImageStacks
 - Display images from Rokka service

## Install and Setup
Due to a hitch in the dependency resolution from the modules, the "composer_manager" module
must be installed first, and only later the "rokka" module.

 - `drush en composer_manager -y`
 - `drush en rokka -y`

Most module configuration is handled at `admin/config/media/rokka`.
 
### Patches for full functionality
 
 While Drupal core and contrib have basic support for remote stream wrappers, most modules have issues where they hard
 code URIs or specific file systems. All of these patches add simple alter hooks, so they should be unlikely to cause
 problems.
 
 - Image styles Paths: [hook_image_style_path_alter](https://www.drupal.org/node/1358896#comment-9297197) needs to be
    patched in Drupal's image.module. (**REQUIRED**)
 - Image styles URIs QueryTokens: [hook_image_style_uri_token_query_alter](https://www.drupal.org/node/2610308) needs to
    be patched in Drupal's image.module to avoid CDN issues with Rokka.

## Features
 - Rokka access credentials configuration and validation
 - Listing of currently available SourceImages on Rokka
 - Listing of currently available ImageStacks on Rokka with details
 - Map Drupal image-effects to Rokka: image styles are automatically converted and saved to Rokka
 - Batch move images to Rokka.io from existing fields (install the `rokka_massmover` submodule)
 - Expose Rokka.io Operations as Drupal image-effects (install the `rokka_effects` submodule)
 
## Additional Rokka-only Image Effects
Please install the `rokka_effects` submodule.
  - *Crop with Background*: allow images to be cropped with a bigger size than the original image, configurable background color and transparency
  - *Blur*: apply blur effect to the image (un-sharpening)  

## Third-Party modules integration
 The Rokka module has been successfully tested with the following third-party modules:
 - [FocalPoint](http://www.drupal.org/project/focal_point) module (version 7.x-1.0-beta6)
 - [Media](http://www.drupal.org/project/media) module
 - [Picture](http://www.drupal.org/project/picture) module (version 7.x-2.x-dev, after issue [#2610318](https://www.drupal.org/node/2610318) got merged)

## Image Effects support:
This is a preliminary list of image effects supported by Rokka and the Rokka module:

 - Core Drupal "Crop"
 - Core Drupal "Resize"
 - Core Drupal "Rotate"
 - Core Drupal "Scale"
 - Core Drupal "ScaleAndCrop"
 - Focal_Point: "Crop"

## ToDo(s)
 - Integrate effects from:
   - https://www.drupal.org/project/imagecache_actions
   - https://www.drupal.org/project/filtersie
 - Replace SourceImage list with a View
