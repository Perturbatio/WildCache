# WildCache

[![CircleCI](https://circleci.com/gh/Perturbatio/WildCache/tree/master.svg?style=shield)](https://circleci.com/gh/Perturbatio/WildCache/tree/master)
[![Latest Unstable Version](https://poser.pugx.org/perturbatio/wildcache/v/unstable?format=flat)](https://packagist.org/packages/perturbatio/wildcache)
[![License](https://poser.pugx.org/perturbatio/wildcache/license?format=flat)](https://packagist.org/packages/perturbatio/wildcache)
[![Total Downloads](https://poser.pugx.org/perturbatio/wildcache/downloads?format=flat)](https://packagist.org/packages/perturbatio/wildcache)

Adds the ability to find or remove items in the Laravel cache by wildcard.

## Usage
Using WildCache, you can store items using a `dot.notation` syntax, then retrieve the item 
matching to get or remove all items matching a pattern.

## Methods

### Put - Write a value to cache

```PHP
/** @var \Perturbatio\WildCache\WildCache $wildCache */
$wildCache = app('wildcache');
// use the WildCache to store the item with a dot separated key
$wildCache->put('test.WildCache.itemA', 9999, now()->addMinutes(10));
$wildCache->put('test.WildCache.itemB', 8888, now()->addMinutes(10));

```
### Get

The get method always returns a collection of any items that match the pattern, or the default value passed in
 (defaults to `null`)

```PHP
/** @var \Perturbatio\WildCache\WildCache $wildCache */
$wildCache = app('wildcache');
// returns the first item that has a key prefixed with `test.WildCache.`
echo $wildCache->get('test.WildCache.*')->first(); 
```

```PHP
/** @var \Perturbatio\WildCache\WildCache $wildCache */
$wildCache = app('wildcache');
echo $wildCache->get('some.key', 'default_value')->first();
```

```PHP
/** @var \Perturbatio\WildCache\WildCache $wildCache */
$wildCache = app('wildcache');

// use the WildCache to store the item with a dot separated key
$wildCache->put('test.WildCache.itemA', 9999, now()->addMinutes(10));
$wildCache->put('test.WildCache.itemB', 8888, now()->addMinutes(10));

// retrieve the first that matches the key
echo $wildCache->get('test.WildCache.*')->first(); // 9999
echo $wildCache->get('test.WildCache.*')->get('wildcache.test.itemB');
```