# php-markdown-compare

Main idea is compare PHP Markdown parsers/converters, 
find which one is faster and have a smallest memory footprint.
           

## Reasons
                                 
1. Cannot find normal independent comparison, including different versions.
2. Existing comparison, 
   [league/commonmark script](https://github.com/thephpleague/commonmark/blob/2.4/tests/benchmark/benchmark.php),
   over-engineered (too complex) and not enough isolated.
3. No results published
4. No comparison of different engine supported abilities (TBD).  
                                                               

## Current versions compared

1. [league/commonmark](https://github.com/thephpleague/commonmark), version 2.4.2
   - alias `cm`: `League\CommonMark\CommonMarkConverter`
   - alias `cm-gfm`: `League\CommonMark\GithubFlavoredMarkdownConverter`
   - alias `cm-all`: `League\CommonMark\MarkdownConverter` + all possible plugins
2. [Parsedown](https://github.com/erusev/parsedown)
   - alias `pd-17`: version 1.7.4 / stable
   - alias `pd-18`: version 1.8.0-beta-7 / beta
   - alias `pd-20`: version 2.0.0-dev / dev
3. [cebe/markdown](https://github.com/cebe/markdown), version 1.2.1
   - alias `cebe-md`: `cebe\markdown\Markdown`
   - alias `cebe-md-gfm`: `cebe\markdown\GithubMarkdown`
   - alias `cebe-md-extra`: `cebe\markdown\MarkdownExtra`
   
## Current results

Date: 2024-02-29  
OS: Linux

    $ php -d xdebug.mode=off run.php -memory --csv test.csv
    Running Benchmarks Isolated, 9 Implementations, 20 Iterations:
       cm                             ....................
       cm-gfm                         ....................
       cm-all                         ....................
       pd-17                          ....................
       pd-18                          ....................
       pd-20                          ....................
       cebe-md                        ....................
       cebe-md-gfm                    ....................
       cebe-md-extra                  ....................
    Benchmark Results, CPU:
       1. pd-17                         4,29 ms        top
       2. cebe-md                       4,57 ms      +6,5%
       3. cebe-md-extra                 5,36 ms     +24,9%
       4. pd-18                         7,22 ms       x1,7
       5. cebe-md-gfm                   7,37 ms       x1,7
       6. pd-20                        17,18 ms       x4,0
       7. cm                           39,61 ms       x9,2
       8. cm-gfm                       41,95 ms       x9,8
       9. cm-all                       61,02 ms      x14,2
    Benchmark Results, Peak Memory:
       1. pd-18                        1.155 kB        top
       2. pd-17                        1.191 kB      +3,1%
       3. cebe-md                      1.195 kB      +3,4%
       4. cebe-md-extra                1.281 kB     +10,9%
       5. cebe-md-gfm                  1.480 kB     +28,1%
       6. pd-20                        1.617 kB       x1,4
       7. cm                           5.700 kB       x4,9
       8. cm-gfm                       6.042 kB       x5,2
       9. cm-all                       6.520 kB       x5,6
