# ListingTools

CLI tools for indexing, extacting, and  embedding code listings. Originally dogfood code written for PHP Object Patterns and Practice.

ListingTools helps you to

* keep your sample code in a runnable and testable state
* find all listings easily in your code archive
* insert code examples into your article, blog post, or chapter
* reimport updated/improved listings easily
* renumber and reflow code examples

## Installation
There are three easy options

# Install as a project with composer

If your current project does not have a composer file (and you don't want to create one), run `composer create-project` to get the tools in a subdirectory.

```
$ composer create-project getinstance/listingtools
```

This will generate a `listingtools` directory. You can invoke the tools from anywhere.

```
$ php listingtools/scripts/doindex.php myrepo/
```

# Download and unzip

You can find the latest release at https://github.com/getinstancemz/listingtools/releases

Download and uncompress the source code archive and run as above.

> **NOTE** Since the dependency won't be enforced for installation using this method, it is important to be aware that the project requires PHP 8.1. 

# Add to a composer-based project

```
$ composer require getinstance/listingtools
$ composer update
```

The tools will be added to your `vendor` directory. Scripts can be accessed via `vendor/bin`

```
$ ./vendor/bin/doindex.php myrepo/
```

> **NOTE** Because the scripts are installed as composer binaries you do not need to explicitly invoke PHP.

## Quick start
Add comments to your source repository defining the code blocks you wish to extract. You can use slash-star comments although hash comment, HTML comments and a custom json element are also supported

```php
/* listing 001.01 */
    public function getMatches()
    {
        return $this->output;
    }
/* /listing 001.01 */

/* listing 001.02 */
    public function reset()
    {
        $this->reading = [];
        $this->output = [];
    }
/* /listing 001.02 */
```
Create content slots in your chapter/article

```
As you can see here `getMatches()` will give you access to found listings

<!-- listing 001.01 -->
<!-- endlisting -->

When client code calls this...
```

Run the `gencode.php` command to generate the code blocks and insert them into your manuscript (always do this after committing your work so that you can roll back if necessary).

```
./vendor/bin/gencode.php readme src/ README.md README.md
```

Those arguments are: an arbitrary namespace for your project (this is required but only used with the GitHub gist feature), the directory of your source repositiory, your article or chapter, your output file. If you do not specify an output file then the command will write to STDOUT.

Your code slot will then be filled with the corresponding code as marked in your code comments:

    As you can see here `getMatches()` will give you access to found listings

    <!-- listing 001.01  -->
    ```php
    public function getMatches()
    {
        return $this->output;
    }

    ```
    <!-- endlisting -->

    When client code calls this...

If you need to improve your source code, fix it in the repository and not the manuscript, then run `gencode.php` again -- your listings will be updated.

## The tools
The quick start section demonstrates some useful functionality, but it also begs some questions. How do you insert a new listing, for example, without having to hand-renumber all the listings in your chapter? For my book, some chapters contain approximately a hundred listings -- adding inserting three or four new listings would have been... well it's the kind of work that any programmer would rather automate than do manually -- which is why I'm writing this document now. Anyway, here are the details.

### doindex.php
Generate an index of all listings marked in the referenced repo

```
doindex.php <file_or_dir>
```
#### Arguments

| **Argument** | **Description** | **Required?** |
|----------|-------------|-----------|
| file\_or\_dir | A source file or repository containing listings comments | yes |

#### Side effects
None. Entirely read only. Does not write a cache.

#### Notes
This command provides a useful overview of listings during development -- ordered by article.listing number. Can also be usedd to generate an index for readers where a code archive is to be offered alongside a publication.

## Generate an index

```
$ doindex src/

001.01: 
    src/output/Parser.php
001.02: 
    src/output/Parser.php
```


