@props([
    'title' => 'Best practices for successful prototypes',
    'author' => 'Jese Leos',
    'authorRole' => 'Graphic Designer, educator & CEO Flowbite',
    'authorAvatar' => 'https://flowbite.com/docs/images/people/profile-picture-2.jpg',
    'publishDate' => 'Feb. 8, 2022',
    'publishDatetime' => '2022-02-08',
    'category' => 'Tutorial',
])

<main {{ $attributes->merge(['class' => 'pt-8 pb-16 lg:pt-16 lg:pb-24 bg-white dark:bg-gray-900 antialiased']) }}>
    <div class="flex justify-between px-4 mx-auto max-w-screen-xl">
        <article class="mx-auto w-full max-w-2xl format format-sm sm:format-base lg:format-lg format-blue dark:format-invert">
            <header class="mb-4 lg:mb-6 not-format">
                <address class="flex items-center mb-6 not-italic">
                    <div class="inline-flex items-center mr-3 text-sm text-gray-900 dark:text-white">
                        <img class="mr-4 w-16 h-16 rounded-full" src="{{ $authorAvatar }}" alt="{{ $author }}">
                        <div>
                            <a href="#" rel="author" class="text-xl font-bold text-gray-900 dark:text-white">{{ $author }}</a>
                            <p class="text-base text-gray-500 dark:text-gray-400">{{ $authorRole }}</p>
                            <p class="text-base text-gray-500 dark:text-gray-400">
                                <time pubdate datetime="{{ $publishDatetime }}" title="{{ $publishDate }}">{{ $publishDate }}</time>
                            </p>
                        </div>
                    </div>
                </address>
                <h1 class="mb-4 text-3xl font-extrabold leading-tight text-gray-900 lg:mb-6 lg:text-4xl dark:text-white">{{ $title }}</h1>
            </header>

            {{ $slot }}

            @if($slot->isEmpty())
            <p class="lead">Flowbite is an open-source library of UI components built with the utility-first classes from Tailwind CSS. It also includes interactive elements such as dropdowns, modals, datepickers.</p>
            <p>Before going digital, you might benefit from scribbling down some ideas in a sketchbook. This way, you can think things through before committing to an actual design project.</p>
            <p>But then I found a <a href="#">component library based on Tailwind CSS called Flowbite</a>. It comes with the most commonly used UI components, such as buttons, navigation bars, cards, form elements, and more which are conveniently built with the utility classes from Tailwind CSS.</p>
            <figure>
                <img src="https://flowbite.s3.amazonaws.com/typography-plugin/typography-image-1.png" alt="Digital art workspace">
                <figcaption>Digital art by <a href="#">Unsplash</a></figcaption>
            </figure>
            <h2>Getting started with Flowbite</h2>
            <p>First of all you need to understand how Flowbite works. This library is not another framework. Rather, it is a set of components based on Tailwind CSS that you can just copy-paste from the documentation.</p>
            <ol>
                <li><strong>Usability testing</strong>. Does your user know how to exit out of screens? Can they follow your intended user journey and buy something off the site you have designed? By게 running a usability test, you'll be able to see how users will interact with your design once it's live.</li>
                <li><strong>&nbsp;Involving stakeholders</strong>. Project stakeholders need to be aligned on the key deliverables before implementation starts. This includes the project timeline, key milestones, and budgets.</li>
                <li><strong>A/B testing</strong>. A/B testing can be a great way to test the effectiveness of different design approaches within the same project scope.</li>
            </ol>
            <h3>Key takeaways</h3>
            <p>A design system is a set of guidelines, principles, and assets that streamline the design and development process. It ensures consistency across products while accelerating time-to-market.</p>
            @endif

            @if(isset($share))
            <div class="not-format">
                {{ $share }}
            </div>
            @else
            <div class="flex items-center space-x-4 not-format mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Share this article</span>
                <button type="button" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <x-fwb-s-facebook class="w-5 h-5" />
                    <span class="sr-only">Share on Facebook</span>
                </button>
                <button type="button" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <x-fwb-s-twitter class="w-5 h-5" />
                    <span class="sr-only">Share on Twitter</span>
                </button>
                <button type="button" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <x-fwb-s-github class="w-5 h-5" />
                    <span class="sr-only">Share on GitHub</span>
                </button>
                <button type="button" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 dark:bg-gray-900 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <x-fwb-s-x-company class="w-5 h-5" />
                    <span class="sr-only">Share on X</span>
                </button>
            </div>
            @endif
        </article>

        @if(isset($sidebar))
        <aside class="hidden xl:block xl:w-80" aria-label="Sidebar">
            <div class="sticky top-6">
                {{ $sidebar }}
            </div>
        </aside>
        @endif
    </div>

    @if(isset($relatedPosts))
    <div class="px-4 mx-auto max-w-screen-xl mt-12 pt-12 border-t border-gray-200 dark:border-gray-700">
        <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Related articles</h2>
        <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
            {{ $relatedPosts }}
        </div>
    </div>
    @endif
</main>
