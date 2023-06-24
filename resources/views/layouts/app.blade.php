<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="/css/dashboard.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <script>
            $(document).ready(function() {
                $("#select_page").select2();

                $("#select_page").on("change", function() {
                    let selectedValue = $(this).val();
                    let selectedText = $(this).find("option:selected").text();
                    console.log("Selected value:", selectedValue);
                    console.log("Selected text:", selectedText);

                    let addUserButton = $('button[class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ml-3 p-3"]');



                    if (selectedValue !== "") {
                        // variables
                        let select = $("#select_page");
                        let parentform = $("#forms");
                        let parentDivCrypto = $("#cryptos");
                        // creatingElements
                        let cryptoListItem = $("<div>").text(selectedText).addClass("cryptoContainerList");
                        let trashIcon = $("<i>").addClass("fa-solid fa-trash");

                        let inputHiddenName = $("<input>")
                            .attr("type", "text")
                            .val(selectedText)
                            .addClass("dn")
                            .attr("name", 'tokenCryptoName');

                        let inputHidden = $("<input>")
                            .attr("type", "text")
                            .val(selectedValue)
                            .addClass("dn")
                            .attr("name", 'tokenCrypto');
                        let divButton = $("<div>")
                            .addClass("flex items-center justify-end mt-4");

                        let label = $("<label>")
                            .addClass("block font-medium text-sm text-gray-700 dark:text-gray-300")
                            .text("User's token for" + selectedText + " (You can leave more than 1 user's token by seperating space)");

                        let inputUserToken = $("<input>")
                            .addClass("border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full p-3")
                            .attr('name', "usersToken")
                            .attr('placeholder', "Enter User's token for")
                            .attr('style', "padding: 10px;");

                        let button = $("<button>")
                            .text("Start getting info")
                            .attr('type', 'submit')
                            .addClass("inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ml-3 p-3");

                        let addForm = $('<form>')
                            .attr("method", 'POST')
                            .attr("action", '/sendTG');

                        // actions
                        select.find("option[value='" + selectedValue + "']")
                            .prop("disabled", true);

                        divButton.append(button);
                        addForm.append(label);
                        addForm.append($('<input>').attr('type', 'hidden').attr('name', '_token').val('{{ csrf_token() }}'));
                        addForm.attr('id', selectedValue);
                        addForm.append(inputUserToken);
                        addForm.append(divButton);
                        addForm.append(inputHidden);
                        addForm.append(inputHiddenName);

                        cryptoListItem.append(trashIcon);
                        parentDivCrypto.append(cryptoListItem);
                        parentform.append(addForm.clone());

                        // delete by clicking trash icon
                        trashIcon.on("click", function() {
                            cryptoListItem.remove();

                            $("#select_page").find("option[value='" + selectedValue + "']").prop("disabled", false).removeAttr("disabled");

                            $('#' + selectedValue.replace(/[^a-zA-Z0-9-_]/g, '\\$&')).remove();
                        });
                        
                    }
                });
            });
        </script>
    </body>

</html>
