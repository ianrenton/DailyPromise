Daily Promise
=============

Whether it's chores, diets, jobs or simply finding time to relax, Daily Promise will help keep you in check!

This is the code that powers the [http://dp.onlydreaming.net](http://dp.onlydreaming.net) website.

The code is licenced under the BSD licence.  More details at [http://dp.onlydreaming.net/about](http://dp.onlydreaming.net/about).

I wrote this code a long time ago, and the quality of it it pretty bad, but it is functional. I believe recent changes to the Twitter API have not broken the login mechanism, which is Twitter OAuth based.

Install on Heroku
-----------------

* Set up a MySQL database somewhere
* Run:

```
    git clone https://github.com/ianrenton/DailyPromise.git
    cd DailyPromise
    cp sample.env .env
```
* Edit `.env` in your favourite editor
* Run:

```
    heroku apps:create
    heroku config:push
```
