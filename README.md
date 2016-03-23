# Rokka: Drupal integration Module

This module integrates [Rokka.io](https://rokka.io) with Drupal: after setting up your credentials the module allows to:

 - Automatically upload images from fields to Rokka by using the `rokka://` stream wrapper
 - Synchronize Drupal's Image Styles to Rokka's ImageStacks
 - Display images from Rokka service

## Setup
Most module configuration is handled at `admin/config/media/rokka`.
 
### Patches for full functionality
 
 While Drupal core and contrib have basic support for remote stream wrappers, most modules have issues where they hard
 code URIs or specific file systems. All of these patches add simple alter hooks, so they should be unlikely to cause
 problems.
 
 - Image styles Paths: [hook_image_style_path_alter](https://www.drupal.org/node/1358896#comment-9297197) needs to be
    patched in Drupal's image.module. (**REQUIRED**)
 - Image styles URIs QueryTokens: [hook_image_style_uri_token_query_alter](https://www.drupal.org/node/2610308) needs to
    be patched in Drupal's image.module to avoid CDN issues with Rokka.

## Available Features
 - Rokka access credentials configuration and validation
 - Listing of currently available SourceImages on Rokka
 - Listing of currently available ImageStacks on Rokka with details
 - Image Styles synchronization with Rokka's ImageStacks: image styles are automatically converted and saved to Rokka as
    ImageStacks, most of the image Effects are translated to a compatible Rokka's [Operations](https://rokka.io/documentation/references/operations.html).
    
## Third-Party modules integration
 The Rokka module has been successfully tested with the following third-party modules:
 - [FocalPoint](http://www.drupal.org/project/focal_point) module (version 7.x-1.0-beta6)
 - [Media](http://www.drupal.org/project/media) module
 - [Picture](http://www.drupal.org/project/picture) module (version 7.x-2.x-dev, after issue [#2610318](https://www.drupal.org/node/2610318) got merged)

## ToDo(s)
 - Replace SourceImage list with a View
 - Include a link to the account usage-plan details on Rokka.io
