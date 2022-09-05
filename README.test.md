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

Add your listings to your source archive

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

Generate an index

```
$ doindex src/

001.01: 
    src/output/Parser.php
001.02: 
    src/output/Parser.php
```

Create content slots in your chapter/article

    As you can see here `getMatches()` will give you access to found listings

    <!-- listing 001.01  -->
    ```php
    public function getMatches()
    {
        return $this->output;
    }
    ```
    <!-- endlisting -->

    You can reset the parser to read another source file

    <!-- listing 001.01  -->
    ```php
    public function getMatches()
    {
        return $this->output;
    }

    ```
    <!-- endlisting -->

You can then
