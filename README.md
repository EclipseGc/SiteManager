SiteManager:
============

What is this?:

Site Manager is a sort of sandboxed attempt at learning and hopefully to some degree "fixing" various things about Drupal
core that have always bothered me. During the 8.x release cycle we have done a lot to both integrate Symfony's routing
and to improve our existing "Entity" system. Great strides have been made in both these areas, and my work here is not
meant as a commentary or criticism of what exists in Drupal 8. What I've created here is a ground up rewrite using
existing Symfony & Drupal components. It makes no considerations for Drupal's bootstrap process, installation or testing.
With that in mind, what does exist here is a cohesive experiment in which I have tried to solve the interactions of
routing & entities, and also an attempt at simplifying entities to some degree in order to make them more flexible,
easier to follow, and ultimately more important to the whole of the code base.

Getting Your Barings:

As of right now, everything interesting exists in vendor/sitemanager/SiteManager/Core. Before I dissect what's in there,
it's worth pointing out that a number of Drupal systems are incorporated into this. 

1.) The database layer of Drupal is present and largely working. There are a handful of un-generic elements to that code,
but it's so isolated from the day to day usage that you don't notice. I have hacked it very very little and only with
regard to t()/String::format() usage.

2.) The Plugin Component is also used here. There are dependencies within that on the Annotation & Reflection components,
which should likely be subsumed by Plugins or Utilities.

3.) The Utility Component is also present since Plugins has a couple of direct dependencies on it.

With those things laid out, the various symfony components are all well documented within our composer.json file. Some of
these see heavy use, while others I am just dabbling with. All the custom code is really in SiteManager\Core.

SiteManager\Core:

The code here is admittedly jumbled for the time being. I would like to begin phasing it all into its own stand alone
components. But that has not begun. The major portions of this system are all represented by Plugin Managers:

1.) The TableManager:
TableManager's job is to find anything that needs to build a table in the databse. This could include various classes
that implement TableSchemaInterface, or Context plugins that have added schema definition to their plugin definition.
There is only one plugin for this currently and it finds all available schemas for all Context plugins. Others could
easily be written.

2.) The ContextManager:
The ContextManager is the true backbone of the entire system. Contexts are my own take on Drupal's Entity system. It's
heavily inspired by, not a direct descendent of the entity system. Ultimately I would like to begin implementing existing
Drupal entities in the Context system to prove that it is as functional as Drupal's existing system, but for the time
being, I have kept things fairly simple. The ContextManager functions by finding Context plugins. Any "Context" directory
directly in any PSR-0 namespaced directory should be elegible to provide Context plugins. We currently provide 2, the
Site & Route contexts. Site is ridiculously simple, gives us an example of a serially keyed entity, and the general
notion of a "Content Entity". Route is significantly more complex, provides a named primary key, a number of other 
indexes & ultimately, is used in the actual routing process.

Context plugins rely on a predefined service for their "storage controller". This gives us the opportunity to truly
inject storage controllers with their appropriate needs. As an example a sql & yml storage controller & corresponding
service are all provided by default in SiteManager. The sql controller ultimately wants a Drupal\Core\Database\Connection
object injected into it & the yml controller wants a php Directory object injected into it. These things are all set up
as part of the Dependency injection container which can be found in /src/container.php

Storage controllers are given an opportunity to "process" the context plugins using them. The sql controller is a great
example of this as it processes the annotations on each parameter of the Context in order to generate a schema for it.
The yml controller has no such need and make no alterations to the plugin definition. Ultimately I've been contemplating
as Symfony event at this layer to allow any number of event subscribers to process the Context plugins before they are
cached. A great example of this might be a FormProcessor (which doesn't yet exist). This could add form definitions for
Symfony's Form component so that a generic "Form Controller" analogy could be written for all Contexts simultaneously.
I have only just begun to play with Symfony's Form component, so I have no clue how realistic this might be.

Finally it's worth noting, I am not currently attempting to cache the Context definitions, so all this processing is
happening ob every request. That's just me being lazy, not an actual requirement of the system.

3.) The RouteManager:
The RouteManager is one of the bread & butter components that I feel is a really good mix of Drupal & Symfony. We define
Route Plugins as any properly annotated class that appears in a "Route" directory in a PSR-0 namespace dir. These are
easy to build and really just require a render method to be met. They utilize the Plugin Context system in order to
inject context from the resolved route into the actual plugin. The SiteCommands plugin is a great example. It uses the
same naming structure for the context it wants and the parameter in the url that can solve it. The RouteManager leverages
the ContextManager and the byproduct is a dead simple upcasting process from a serial number to a full Site Context. This
can also be used to get empty contexts if there is no "upcasting" process involved. the CreateSite route is a good
example of this process.

Ultimately, the robustness of the service as a storage controller for Contexts lets us swap controller pretty easily. At
various times the Route context may be using yml or sql just depending on what I was testing that day. This shouldn't,
have any super noticeable effect for most people just playing with the system (beyond getting permissions on directories
setup properly.

The rest of the Routing layer leverages Symfony in the purest manner I could accomodate. We get HttpCache/Kernel/Etc all
working together & we have a custom EventSubscriber that is subscribed to the kernel.request::onResponse event. When that
fires our custom ControllerResolver class checks to see if the route in question has a corresponding plugin & generally
conforms to what we expect plugins to look like. If it passes those tests, then it leverages the Route plugin system
to resolve the controller completely. Otherwise this system runs through Symfony's normal controller resolver class.
There is currently no way to make non-plugin based RouteCollections available to the system, but I'd be really interested
in proving that it can be sanely done and that it interfaces to normal Syfmony workflows. (Or making it do so if it
doesn't)

Beyond these layers I am just dabbling in things like the afore-mentioned Form Component & how that interacts with my
Contexts.

Installation:
=============

1.) Checkout the master branch of this repo.

2.) curl -sS https://getcomposer.org/installer | php

3.) php composer.phar install

4.) add database credentials to sites/default/dbconneciton.php

5.) setup /web/index.php as your site index.

6.) visit /install.php in your browser

7.) visit /routeRebuild.php in your browser.

8.) visit /index.php

You shouldn't actually see anything in step 8. Currently that page is attempting to display all "site" contexts and
since you don't have any, obviously it can't do anything. There's a little code in the SiteCommands route that you could
use to get a new site context created if you want. From there you should be able to visit /site/{site}. I'm still working
on anything beyond that, but this should give you a nice minimal use case to see how the code basically works.

Code Flow:
==========

The basic flow of the system attempts to emulate what I've seen in various Symfony systems and tutorials. At the same time,
there are some clear concessions to Drupal's use cases, and I'm trying to keep them in mind as I develop here. Opening up
the /web/index.php file, the first line includes our dependency injection container. This functions largely in place of
what we might think of as a "bootstrap" process. It is only mildly analogous, but it's all we have in this scenario. Within
the container, database connections, configuration directories and all the relevant class architecture are setup.

The index goes on to create a request object from global values and then leverages the RouteManager to determine which
route matches the current request. The RouteManager::matchRoute() method leverages the ContextManager and asks it to
generate a route collection for routes that matches the exact path. If that collection is empty, it them begins to break
down the current request path piece by piece and attempts to generate RouteCollections for subsets of the request path.
As an example, if you had a node/{node} path pattern on a route plugin, during registration of routes, that path would be
broken down to the most basic component that doesn't contain a variable. In our case this would be simply 'node'. That is
stored along side the rest of the route and can be indexed for faster queries. When the route node/1 is requested, no
exact match will exist for node/1, but the RouteManager::matchRoute() method will begin to remove elements of the path in
order to see if we can match a smaller subset. Getting a route that has 'path_root' of node and including it into our
collection for matching purposes makes for a smaller collection. The longer the full route, the longer this process could
take. Finally, if all else fails we have an empty else statement that I'm considering putting an event into. This could
potentially give other Symfony systems that don't leverage the Route plugin the ability to provide their own collections
for a given request.

Once a route is matched, its information is added to the request object and passed to Symfony's HttpKernel (actually the
cache, but I digress). HttpKernel will fire the kernel.request::onResponse event to which our PluginRouterListener is
listening. That will hand off the request to a custom ControllerResolver that will determine if the current requested
route is provided by a plugin at which point it will return a response from a plugin if available.

Route Plugins provide responses as part of their interface. The route in question will generate a response and pass it
back, which is ultimately run through the normal Response->send() methodology.

