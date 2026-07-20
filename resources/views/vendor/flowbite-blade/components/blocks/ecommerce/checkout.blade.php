@props([
    'title' => '',
    'action' => '#',
])

<section {{ $attributes->merge(['class' => 'bg-white py-8 antialiased dark:bg-gray-900 md:py-16']) }}>
    <form action="{{ $action }}" class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        {{-- Progress steps --}}
        @if(isset($steps))
            {{ $steps }}
        @else
            <ol class="flex w-full max-w-2xl items-center text-center text-sm font-medium text-gray-500 dark:text-gray-400 sm:text-base">
                <li class="after:border-1 flex items-center text-blue-700 after:mx-6 after:hidden after:h-1 after:w-full after:border-b after:border-gray-200 dark:text-blue-500 dark:after:border-gray-700 sm:after:inline-block sm:after:content-[''] md:w-full xl:after:mx-10">
                    <span class="flex items-center after:mx-2 after:text-gray-200 after:content-['/'] dark:after:text-gray-500 sm:after:hidden">
                        <x-fwb-o-check-circle class="me-2 h-4 w-4 sm:h-5 sm:w-5" />
                        Cart
                    </span>
                </li>
                <li class="after:border-1 flex items-center text-blue-700 after:mx-6 after:hidden after:h-1 after:w-full after:border-b after:border-gray-200 dark:text-blue-500 dark:after:border-gray-700 sm:after:inline-block sm:after:content-[''] md:w-full xl:after:mx-10">
                    <span class="flex items-center after:mx-2 after:text-gray-200 after:content-['/'] dark:after:text-gray-500 sm:after:hidden">
                        <x-fwb-o-check-circle class="me-2 h-4 w-4 sm:h-5 sm:w-5" />
                        Checkout
                    </span>
                </li>
                <li class="flex shrink-0 items-center">
                    <x-fwb-o-check-circle class="me-2 h-4 w-4 sm:h-5 sm:w-5" />
                    Order summary
                </li>
            </ol>
        @endif

        <div class="mt-6 sm:mt-8 lg:flex lg:items-start lg:gap-12 xl:gap-16">
            {{-- Form section --}}
            <div class="min-w-0 flex-1 space-y-8">
                @if(isset($form))
                    {{ $form }}
                @else
                    <div class="space-y-4">
                        @if ($title)
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                        @endif

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="your_name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Your name</label>
                                <input type="text" id="your_name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="Bonnie Green" required />
                            </div>

                            <div>
                                <label for="your_email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Your email*</label>
                                <input type="email" id="your_email" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="name@flowbite.com" required />
                            </div>

                            <div>
                                <label for="select-country" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Country*</label>
                                <select id="select-country" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                    <option selected>United States</option>
                                    <option value="AS">Australia</option>
                                    <option value="FR">France</option>
                                    <option value="ES">Spain</option>
                                    <option value="UK">United Kingdom</option>
                                </select>
                            </div>

                            <div>
                                <label for="select-city" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">City*</label>
                                <select id="select-city" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                    <option selected>San Francisco</option>
                                    <option value="NY">New York</option>
                                    <option value="LA">Los Angeles</option>
                                    <option value="CH">Chicago</option>
                                    <option value="HU">Houston</option>
                                </select>
                            </div>

                            <div>
                                <label for="phone-input" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Phone Number*</label>
                                <input type="text" id="phone-input" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="123-456-7890" required />
                            </div>

                            <div>
                                <label for="company_name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Company name</label>
                                <input type="text" id="company_name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="Flowbite LLC" />
                            </div>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Payment</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 ps-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="credit-card" type="radio" name="payment-method" class="h-4 w-4 border-gray-300 bg-white text-blue-600 focus:ring-2 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" checked />
                                    </div>
                                    <div class="ms-4 text-sm">
                                        <label for="credit-card" class="font-medium leading-none text-gray-900 dark:text-white">Credit Card</label>
                                        <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Pay with your credit card</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 ps-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="pay-on-delivery" type="radio" name="payment-method" class="h-4 w-4 border-gray-300 bg-white text-blue-600 focus:ring-2 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" />
                                    </div>
                                    <div class="ms-4 text-sm">
                                        <label for="pay-on-delivery" class="font-medium leading-none text-gray-900 dark:text-white">Payment on delivery</label>
                                        <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">+$15 payment processing fee</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 ps-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="paypal" type="radio" name="payment-method" class="h-4 w-4 border-gray-300 bg-white text-blue-600 focus:ring-2 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" />
                                    </div>
                                    <div class="ms-4 text-sm">
                                        <label for="paypal" class="font-medium leading-none text-gray-900 dark:text-white">Paypal account</label>
                                        <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Connect to your account</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Methods --}}
                    <div class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delivery Methods</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 ps-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="dhl" type="radio" name="delivery-method" class="h-4 w-4 border-gray-300 bg-white text-blue-600 focus:ring-2 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" checked />
                                    </div>
                                    <div class="ms-4 text-sm">
                                        <label for="dhl" class="font-medium leading-none text-gray-900 dark:text-white">$15 - DHL Fast Delivery</label>
                                        <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Get it by Tomorrow</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 ps-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="fedex" type="radio" name="delivery-method" class="h-4 w-4 border-gray-300 bg-white text-blue-600 focus:ring-2 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" />
                                    </div>
                                    <div class="ms-4 text-sm">
                                        <label for="fedex" class="font-medium leading-none text-gray-900 dark:text-white">Free Delivery - FedEx</label>
                                        <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Get it by Friday, 13 Dec 2023</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 ps-4 dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-start">
                                    <div class="flex h-5 items-center">
                                        <input id="express" type="radio" name="delivery-method" class="h-4 w-4 border-gray-300 bg-white text-blue-600 focus:ring-2 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600" />
                                    </div>
                                    <div class="ms-4 text-sm">
                                        <label for="express" class="font-medium leading-none text-gray-900 dark:text-white">$49 - Express Delivery</label>
                                        <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Get it today</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Voucher --}}
                    <div>
                        <label for="voucher" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Enter a gift card, voucher or promotional code</label>
                        <div class="flex max-w-md items-center gap-4">
                            <input type="text" id="voucher" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500" placeholder="" />
                            <button type="button" class="flex items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Apply</button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order summary --}}
            <div class="mt-6 w-full space-y-6 sm:mt-8 lg:mt-0 lg:max-w-xs xl:max-w-md">
                @if(isset($summary))
                    {{ $summary }}
                @else
                    <div class="flow-root">
                        <div class="-my-3 divide-y divide-gray-200 dark:divide-gray-800">
                            <dl class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">$8,094.00</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Savings</dt>
                                <dd class="text-base font-medium text-green-500">0</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Store Pickup</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">$99</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">$199</dd>
                            </dl>

                            <dl class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">$8,392.00</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Proceed to Payment</button>

                        <p class="text-sm font-normal text-gray-500 dark:text-gray-400">One or more items in your cart require an account. <a href="#" class="font-medium text-blue-700 underline hover:no-underline dark:text-blue-500">Sign in or create an account now.</a></p>
                    </div>
                @endif
            </div>
        </div>
    </form>
</section>
