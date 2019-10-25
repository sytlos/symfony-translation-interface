# Easily translate your app through a simple user interface

![Easily translate your app through a simple user interface](https://raw.githubusercontent.com/hugosoltys/symfony-translation-interface/master/doc/translations-ui-preview.png)

## Why this POC
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

## How it works

All the translations are listed in a table __filterable by translation 
domain and/or locale__.

For each translation, you can see the key, the text translated in the
default locale and an input in which you just have to type the translated
message in the chosen locale.

On the blur of the input, the typed translation will be saved in the 
corresponding file. _(e.g. : if you translate the word `Red` of the `colors` 
domain in `French`, the translated message will be written in your `colors.fr.yml`
file)_.

If the corresponding file does not exists, it will be created in the directory
provided by your `%translator.default_path%` parameter. 

If you want to support more locales, just add them to the `supported_locales`
parameters of your `config/services.yaml` file.

## Required dependencies

- symfony/form
- symfony/translation
- symfony/twig-bundle
- symfony/validator
- symfony/yaml