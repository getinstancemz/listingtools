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

### Install as a project with composer

If your current project does not have a composer file (and you don't want to create one), run `composer create-project` to get the tools in a subdirectory.

```
$ composer create-project getinstance/listingtools
```

This will generate a `listingtools` directory. You can invoke the tools from anywhere.

```
$ php listingtools/scripts/doindex.php myrepo/
```

### Download and unzip

You can find the latest release at https://github.com/getinstancemz/listingtools/releases

Download and uncompress the source code archive and run as above.

> **NOTE** Since the dependency won't be enforced for installation using this method, it is important to be aware that the project requires PHP 8.1. 

### Add to a composer-based project

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
Add comments to your source repository defining the code blocks you wish to extract. You can use slash-star comments although hash comments, HTML comments and a custom json element are also supported

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

<!-- insert 001.01 -->
<!-- endinsert -->

When client code calls this...
```

Run the `gencode.php` command to generate the code blocks and insert them into your manuscript (always do this after committing your work so that you can roll back if necessary).

```
./vendor/bin/gencode.php readme src/ README.md README.md
```

Those arguments are: an arbitrary namespace for your project (this is required but only used with the GitHub gist feature), the directory of your source repositiory, your article or chapter, your output file. If you do not specify an output file then the command will write to STDOUT.

Your code slot will then be filled with the corresponding code as marked in your code comments:

    As you can see here `getMatches()` will give you access to found listings

    <!-- insert 001.01  -->
    ```php
    public function getMatches()
    {
        return $this->output;
    }

    ```
    <!-- endinsert -->

    When client code calls this...

If you need to improve your source code, fix it in the repository and not the manuscript, then run `gencode.php` again -- your listings will be updated.

## The tools
The quick start section demonstrates some useful functionality, but it also begs some questions. How do you insert a new listing, for example, without having to hand-renumber all the listings in your chapter? For my book, some chapters contain approximately a hundred listings -- inserting three or four new listings early in the chapter would have meant... well it's the kind of work that any programmer would rather automate than do manually -- which is why I'm writing this document now. Anyway, here are the details.

### doindex.php
Generate an index of all listings marked in the referenced repo

```
doindex.php <file_or_dir>
```

#### Arguments

| **Argument** | **Description** | **Required?** |
|----------|-------------|-----------|
| **file\_or\_dir** | A source file or repository containing listings comments | yes |

#### Side effects
None. Entirely read only. Does not write a cache.

#### Notes
This command provides a useful overview of listings during development -- ordered by article.listing number. Can also be usedd to generate an index for readers where a code archive is to be offered alongside a publication.

### Example

```
$ doindex src/

001.01: 
    src/output/Parser.php
001.02: 
    src/output/Parser.php
```
### gencode.php
Read the source listings and the manuscript. Match the source listings with the corresponding manuscript slots. Output as directed.

```
gencode.php [options] <project> <srcdir> <chapterfile.md> [<output.md>]
```

> **CAUTION** This command can change your files quite extensively depending upon how it is run. Always commit to a version control system before running.

#### Arguments

| **Argument** | **Description** | **Required?** |
|----------|-------------|-----------|
| **project** | An arbitrary namespace key. Currenly only used in gist mode, but required  | yes |
| **srcdir**  | The code repository containing code and listing comments | yes |
| **chapterfile.md** | The Markdown document to be read | yes |
| **output.md** | The Markdown document to be written to. Can be the same as the input manuscript. Omitting will cause output to be sent to STDOUT | no |


#### Flags

| **flag** | **arguments?** | **description** |
|--------------|-----------------|----------------|
| **r**    | no  |  Reflow. Ignore listing nn.nn and apply listings in sort order. This will update the slot tags as well as their contents |
| **f**    | no  |  Force. Where available slots do not match listings available in -r mode -- apply anyway. Careful! |
| **d**    | no  |  Dry-run. Will show the current occupant of a slot against the incoming code index. Nothing written |
| **g**    | no  |  (experimental - not yet documented) Rather than generate text, will create a github gist and generate the embed code |

#### Side effects
Where an output argument is given, may write extensively to the specified file (unless the `-d` flag is used). If the experimental `-g` flag is used, then the listing code is created or updated as a gist, and the output slot will be given the corresponding gist embed.


#### Notes
This is the business end of ListingTools. It is how the code gets copied from your source repo (which should be the source of authority for code) and into your manuscript. When using it to write output to a manuscript file exercise extreme file. Version control, and not this tool, is your reset button.

#### Example
In dry run mode, the command ouputs a list of matched listings <-> slots but takes no further action

```
$ vendor/bin/gencode.php -d testproj . chapter.md 

001.00.01
001.01
001.02
001.03
```

### nextlist.php
Given a chapter or article number work out what the next listing tag should be

```
nextlist.php <article-id> <dir>
```

#### Arguments

| **Argument** | **Description** | **Required?** |
|----------|-------------|-----------|
| **article-id** | The chapter or article number -- typically a zero padded three digit number - eg `007`  | yes |
| **dir**  | The code repository containing code and listing comments | yes |

#### Side effects
None. Entirely read only. Does not write a cache.

#### Example
To find the next listing in the current directory: 

```
$ php scripts/nextlist.php 001 .
/* listing 001.05 */
/* /listing 001.05 */
```

### output.php
Given the source directory and a listing number collate the listing and write to standard output

```
output.php <srcdir> <listingno>
```

#### Arguments

| **Argument** | **Description** | **Required?** |
|----------|-------------|-----------|
| **srcdir** |  The code repository containing code and listing comments | yes |
| **listingno** |  The listing number to output (eg 001.01) | yes |

#### Side effects
None. Entirely read only. Does not write a cache.

#### Notes
A useful way to check what a listing will look like when broken up within a file or even spread across several files without having to first generate and embed in the manuscript file

#### Example
```
$ vendor/bin/output.php . 001.05

    print "Hello world\n";

```

### renum.php
Renumber all listings in the given source directory following sort order so that they are contiguous. This is a good way to insert new listings.

```
renum.php <dir>
```

> **CAUTION** When run in anger (ie without flags to suppress or redirect output) this command acts recursively on the given directory, potentially altering many files. Never run this on a files that cannot easily be rolled back to a previous state.

#### Arguments
| **Argument** | **Description** | **Required?** |
|----------|-------------|-----------|
| **srcdir** |  The code repository containing code and listing comments | yes |


#### Flags

| **flag** | **arguments?** | **description** |
|--------------|-----------------|----------------|
| **d**    | no  |  Dry-run. Will output a summary of what woul be changed but write nothing to files |
| **o**    | no  |  print changes. Outputs changes to STDOUT - does not write to file |

#### Side effects
Potentially very large. Will recurse through files in the source directory and renumber listings. Always back up before running.

#### Notes
Typically you would use this to handle deletions or additions. You might take out a listing during writing / development so that your index looks like:

```
001.01: 
    ./one.php
001.02: 
    ./one.php
001.03: 
    ./two.php
```

During development we might remove `001.01`. Then we might decide we want to add a new listing before the first. To do that we might create a listing tagged `001.00.01`. Now the index looks like this:

```
001.00.01: 
    ./one.php
001.01: 
    ./one.php
001.03: 
    ./two.php
```

In the manuscript file we should keep slots up to date with listings in source. Finally, though, when we want to clean up our numbering to close gaps and remove additional listing tag clauses, we can run `renum.php`:

```
$ renum .
001.00.01 -> 001.01
   ./test.md
   ./one.php
001.01 -> 001.02
   ./test.md
   ./one.php
no change: 001.03
```

At this point you should run `git diff` or equivalent to confirm the sanity of the process. Then you can run a `gencode.php` reflow (which inserts listings in sort order ignoring and then updating the stipulated slot tags.


```
$ gencode -r myproject ./ test.md test.md
```

Assuming that your slots and listing count match this command should reimport and retag your newly renumbered listings.
