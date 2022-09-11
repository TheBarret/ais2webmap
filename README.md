# ais2webmap
Plots markers from received AIS radio signals


Its still a very basic/alpha stage, to get this working you need the following:
- Download & Compile : https://github.com/dgiardini/rtl-ais
- Run `rtl_ais -h 127.0.0.1 -P 10110`
- Run `php -f parser.php`

Then make a sub or link with your favorite web serving software (apache or nginx) to the `html` folder.
