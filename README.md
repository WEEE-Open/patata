# P.A.T.A.T.A.

*Pubblicatore Aggiornamenti Task Assegnabili e Task Assegnati*

Information display system that shows a to-do list, date and time, random quotes, random stastics from [T.A.R.A.L.L.O.](https://github.com/WEEE-Open/tarallo/) and other stuff.

## Installation

You will need a Tarallo and instance and a Nextcloud instance with Deck.

For the first one, you can get it from the [repository](https://github.com/WEEE-Open/tarallo/) and follow the instructions in the readme to start it.

For the second, you can run the official [container](https://hub.docker.com/_/nextcloud) and follow the instructions in the setup wizard to install it.  
Then, download the Deck application from the web interface.  
Finally, create an "application password" from your

Then, prepare the configuration:

```bash
cp quotes-example.json quotes.json
cp conf-example.php conf.php
nano conf.php # You'll need to set the DECK_* variables at least, the TARALLO_* ones are already good for a development build
```

If you want to find the numeri stack and board IDs, you can access deck-test.php, it will list all boards and stacks it can read with the supplied user and password. 

## License

MIT with two exceptions:

[Bootstrap 4 Dark theme](https://github.com/ForEvolve/bootstrap-dark) not made by us but still under MIT license.

`patata.svg` Google's emoji released under Apache License 2.0 (https://github.com/googlei18n/noto-emoji)
