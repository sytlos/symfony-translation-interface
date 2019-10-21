# Easily translate your app through a simple user interface

In many Symfony projects, you have to support multiple languages
for your users.

In most of the case, you have a default locale in which you work 90%
of the time but there comes a time when you have to translate all your
translation keys in all the supported languages of your application.

You know the stuggles of this. A deployment is required each time your
Product Owner wants to change "Validate" by "Submit" or when you have
to deploy a hotfix because you thought that "Green" was writed "Verre" 
in French.

That's why I developed this POC. It provides a simple UI allowing a
competent user to translate the app by himself. 

All the translations are listed in a table filterable by translation 
domain and/or locale.

For each translation, you can see the key, the text translated in the
default locale and an input in which you just have to type the translated
message in the chosen locale.

The list is built thanks to the `TranslationReaderInterface` of the 
`symfony/translation` package.

The submission is done with the Javascript fetch API, and the writing
is made with the `TranslationWriterInterface` and the Symfony
`Filesystem` Component. 