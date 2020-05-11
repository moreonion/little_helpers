[![Build Status](https://travis-ci.com/moreonion/little_helpers.svg?branch=7.x-2.x)](https://travis-ci.com/moreonion/little_helpers) [![codecov](https://codecov.io/gh/moreonion/little_helpers/branch/7.x-2.x/graph/badge.svg)](https://codecov.io/gh/moreonion/little_helpers)


# Little helpers

*This is pure API module without any UI. You only need to install it, when anoher module depends on it.*

This is a module that provides an additional API for drupal core and various contributed modules that make development more convenient. Additional classes are added as needed.


## Installation

Install this module and its dependencies [like any other Drupal modules](https://www.drupal.org/docs/7/extend/installing-modules).


### Dependencies

* [psr0](https://www.drupal.org/project/psr0)
* [webform](https://www.drupal.org/project/libraries) ≥ 4.0 (only needed if you use the related functionality)


## Usage

Use the classes provided as you see fit. The module uses [psr0](https://www.drupal.org/project/psr0) so you don’t need to care about including the files.


## Features

* `Services\Container`: *services container* that can also be used as plugin loader. A default instance of a global container is provided.
* `DB\Model`: A base-class for simple ORM model classes.
* `Rest\Client`: A simple JSON/REST API client.
* `ArrayConfig::mergeDefaults()`: A utility function that does the right thing™ when merging defaults into config arrays.
* `ElementTree`: Utility methods to apply functions recursively on a Drupal element tree (form API or renderable array).
* `Webform`: Wrapper around webform submissions and webforms for easier access of submitted values.


## Devlopment

Development on this module is happening mainly [on github](https://github.com/moreonion/little_helpers). Feel free to post issues and pull requests there.

*Note: Development branches on drupal.org might be outdated because they are only pushed to in order to tag releases.*
