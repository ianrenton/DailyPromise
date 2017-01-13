Daily Promise
=============

Whether it's chores, diets, jobs or simply finding time to relax, Daily Promise will help keep you in check! This is a web application built on the Twitter API that allows users to make "promises", then fill in whether they've kept them on a day-to-day basis, and compete to be the best.

I wrote this code a long time ago, and the quality of it it pretty bad, but it is functional. The site never really took off, and changes to the Twitter API have since broken the login mechanism, which is Twitter OAuth based. Development of this software has now been discontinued.

Development
-----------

A series of blog posts detail the development of the software:

1. [Daily Promise: Design Sketches](https://ianrenton.com/blog/daily-promise-design-sketches)
2. [Daily Promise: Coming Together](https://ianrenton.com/blog/daily-promise-coming-together)
3. [Daily Promise: Avatars Everywhere!](https://ianrenton.com/blog/daily-promise-avatars-everywhere)
4. [Announcing: Daily Promise!](https://ianrenton.com/blog/announcing-daily-promise/)

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
