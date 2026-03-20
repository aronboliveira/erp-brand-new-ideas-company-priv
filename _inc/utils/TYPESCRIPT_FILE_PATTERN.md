1. Isolate interfaces, types and declares in dedicated {interfaces,types,declares}.d.ts modules to be imported into the concrete implementation modules;
2. Typescript decorators can be considered when procedural preparation of classes/methods/fields can be frequent in the system, but only if a balance of performance gains + code line counting + drying up of the system results in a positive gain;
