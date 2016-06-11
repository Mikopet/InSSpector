# InSSpector
> Ultimate tool for inspecting various game's player screenshots! Written in Silex :-)

For use this tool, first you need to install it.

There are 2 versions you can do that.
1. Using GIT
2. Download zip

ZIP will be created later, so you can use only git yet:
```
git clone https://github.com/Mikopet/InSSpector.git
cd InSSpector
composer install
```

Okay, we have the working code now, set up the webserver:
### Apache
need to enable mod\_rewrite. I dont use apache, so it's not implemented yet, but look the config [here](http://silex.sensiolabs.org/doc/master/web_servers.html#apache)
### nginx
Use default [Silex config](http://silex.sensiolabs.org/doc/master/web_servers.html#nginx)

## Configuring
Make a `config.yml` in the app directory (near composer files), and fill out like this:

```yaml
servers:
    sd:
        name: Search & Destroy
        shots_dir: /path/to/your/shots/
    tdm:
        name: Team Deathmatch
        shots_dir: /path/to/other/shots/
```

Troubleshoot: if doesn't work, check permissions on dir, and files

## Additional features (not fully implemented)

In config YAML you can use a few plus features, like image covering, or shame wall.
For this, you can simply define
`cover: true`
or 
`shame_wall: true`

you need to add a path for your servers, the shame\_dir like you did with shots\_dir
