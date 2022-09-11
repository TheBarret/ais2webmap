# ais2webmap
Plots markers from received AIS radio signals


Its still a very basic/alpha stage, to get this working you need the following:
- Download & Compile : https://github.com/dgiardini/rtl-ais
- Run `rtl_ais -h 127.0.0.1 -P 10110`
- Run `php -f parser.php`

The AIS receiver captures and tunes into the SDR device, spits out data to our `UDP` defined address and port,
then our php script will capture these messages so we create a nice barrier between our device and front-end.

To watch this on the webpage, make a sub or link with your favorite web serving software (apache or nginx) to the `html` folder.
