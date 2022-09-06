# ListingTools

CLI tools for indexing, extacting, and  embedding code listings. Originally dogfood code written for PHP Object Patterns and Practice.

More doco to come I promise.

## Quick start
You want to: 

* keep your sample code in a runnable and testable state
* find all listings easily in your code archive
* insert code examples into your article, blog post, or chapter
* reimport updated/improved listings easily
* renumber and reflow code examples

Add comments to your source repository defining the code blocks you wish to extract.

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

Those arguments are, an arbitrary namespace for your project (this is required but only used with the GitHub gist feature), the directory of your source repositiory, your article or chapter, your output file. If you do not specify an output file then the command will write to STDOUT.

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

If you need to improve your source code fix it in the repository and not the manuscript, then run `gencode.php` again.



## Generate an index

```
$ doindex src/

001.01: 
    src/output/Parser.php
001.02: 
    src/output/Parser.php
```


