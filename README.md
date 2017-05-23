# Mini-Nova

Mini-Nova is a smaller version of Nova and twice the speed. Perfect for simple sites and API's.

## Not included with Mini-Nova
* Cron
* Encrypted sessions (Cookies and Sessions are included)
* Exception Service
* Forensics
* HTML (class)
* Implicit Controllers into Routing
* Modules
* Resource Routes
* Support for SQLite, PostgreSQL and SQL Server
* Themes
* Whoops! (replaced by a simpler Handler)

## Notable Changes

Assets should be placed inside the `webroot` folder.

Layouts files exist in `App\Views\Layouts` this folder is where all layout files should be placed.

->withStatus() removed when `Redirect::to('route')->withStatus('message')`
