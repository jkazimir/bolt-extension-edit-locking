# Edit Locking Extension

This is an extension for Bolt CMS to provide basic edit locking for
preventing overwrites when multiple editors are working on the same
content.

## Warning!

Use this extension at your own risk. It is intended as a proof of
concept or starting point for those looking to add edit locking to
their specific project, but takes no security into consideration. In
its current state, the websocket server will grant a lock to _any_
connection requesting one for content that is not already locked.

### Caveats

Some concessions are made to ensure the application is not crippled in
cases where locking may fail. These are made to prevent unsaved work,
or the complete inability to edit any content.

If the websocket server stops running, the default behavior of
enabling editing for all is assumed. Additionally, in the case where a
network connection is bad, or the websocket connection otherwise closes
after edit mode is enabled but before leaving the edit page, editing
will remain enabled while the server is unaware and may grant another
lock if requested.

## Installing

Simply install as you would any Bolt extension, available as
`jkazimir/edit-locking`.

## Running the websocket server

Once installed, the websocket server will need to be started in order
for edit locking to take effect. Simply use the provided nut command to
start it:

```
app/nut socket:serve
```

Though configurable from the nut command, the script expects the socket
server to be running on the same hostname as itself, port 8080.

While running this directly works for development purposes, it is
recommended to run it under some sort of process monitor, such as
supervisord, when deployed to any non-development environment.
