# Monkeypatch

The system contains a monkey patch which decorates the CanvasReader service. In this monkeypatch, you can define which group series id's to dynamically change with each call.

This is used to patch up assignments which were assigned to a wrong and/or deleted group series. The first id is the original group series id linked to the assignment, and the second is what group series will actually be used.

Change/enable/disable as you see fit.