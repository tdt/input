# The tdt/input/scheduler

The scheduler contains 3 things:

1. Controllers which process incoming HTTP calls. The controllers load the config which is stored in a database. You can add different configurations which have to be ran each X minutes.

2. The scheduler model: stores, loads, deletes, executes input configurations.

3. The worker: the file that needs to be called each time to process the queue. If you have jobs per minute, you should call this file each minute. It will launch all schedules 