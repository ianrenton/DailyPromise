Daily Promise
=============

Whether it's chores, diets, jobs or simply finding time to relax, Daily Promise will help keep you in check!

This is the code that powers the http://dp.onlydreaming.net website.

The code is licenced under the BSD licence.  More details at http://dp.onlydreaming.net/about

Beware, "my first PHP" quality code lies ahead.

Install on Heroku
-----------------

* Set up a MySQL database somewhere
* Run:
    git clone https://github.com/ianrenton/DailyPromise.git
    cd DailyPromise
    cp sample.env .env
* Edit `.env` in your favourite editor
* Run:
    heroku apps:create
    heroku config:push
