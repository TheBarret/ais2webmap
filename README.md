# ais2webmap
Plots markers from received AIS radio signals


Its still a very basic/alpha stage, to get this working you need the following:
- an SDR device (https://www.rtl-sdr.com/buy-rtl-sdr-dvb-t-dongles/)
- Make sure to download the sdr-rtl library (https://packages.debian.org/bullseye/rtl-sdr)
- Download & Compile : https://github.com/dgiardini/rtl-ais
- Setup/import sql table from the `ais.sql` file with your db software (i used MariaDB)
- Run `rtl_ais -h 127.0.0.1 -P 10110`
- Run `php -f parser.php`

![](https://i.imgur.com/96NV6e5.png)

![](https://i.imgur.com/pT2gUQV.png)

The AIS receiver program will attempt to load the RTL library and sets up the SDR device, spits out data to the `UDP` defined address and port,
then our php script will capture these messages so we create a nice barrier between our device and front-end.

Now you want to run the `rtl-ais` in a `nohup <app> &` so it will run even if you log out.
Make sure if you want to stop this, to do it properly with `kill -3 <pid>` and not aggressivly with `kill -9 <pid>`
because you could create a faulty USB device status (unclosed device).

Green markers are recent received signals, the purple markers are stale positions that have not seen any recent update.
This can be due to bad/blocked reception or simply out of reach.

To watch this on the webpage, make a sub or link with your favorite web serving software (apache or nginx) to the `html` folder.
