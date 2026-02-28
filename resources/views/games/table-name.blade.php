<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Название таблиц из игр" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Название таблиц из игр" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="restricted-container-html">
                            <h1 class="text-2xl font-semibold">Название таблиц из игр</h1>

                            <div class="row mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                <div class="card-game rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <img class="h-14 w-14" src="https://www.language.onllyons.com/ru/ru-en/dist/images/other/cards-icon/quiz-game.webp" alt="Задачи" />
                                    <h3 class="mt-3 text-lg font-semibold">
                                        <a class="hover:underline" href="/play/chose/" target="_blank">Задачи</a>
                                    </h3>
                                    <p class="mt-3 text-sm text-slate-600">a_game_text</p>
                                    <p class="text-sm text-slate-600">a_game_success</p>
                                    <p class="text-sm text-slate-600">general_rating_game</p>
                                    <p class="text-sm text-slate-600">gameTextCooldown</p>
                                </div>

                                <div class="card-game rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <img class="h-14 w-14" src="https://www.language.onllyons.com/ru/ru-en/dist/images/other/cards-icon/quiz-game-write-with-keyboard.webp" alt="Написать перевод" />
                                    <h3 class="mt-3 text-lg font-semibold">
                                        <a class="hover:underline" href="/play/spelling-fun/" target="_blank">Написать перевод</a>
                                    </h3>
                                    <p class="mt-3 text-sm text-slate-600">riddlesSpeakQuestions</p>
                                    <p class="text-sm text-slate-600">riddlesSpeakQuestionsAnswerUser</p>
                                    <p class="text-sm text-slate-600">riddlesSpeakSuccess</p>
                                    <p class="text-sm text-slate-600">riddlesSpeakGeneralRating</p>
                                    <p class="text-sm text-slate-600">riddlesSpeakCooldown</p>
                                </div>

                                <div class="card-game rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <img class="h-14 w-14" src="https://www.language.onllyons.com/ru/ru-en/dist/images/other/cards-icon/quiz-game-write-translate.webp" alt="Расшифруйте аудио" />
                                    <h3 class="mt-3 text-lg font-semibold">
                                        <a class="hover:underline" href="/play/riddles-translate/" target="_blank">Расшифруйте аудио</a>
                                    </h3>
                                    <p class="mt-3 text-sm text-slate-600">riddlesTranslateQuestions</p>
                                    <p class="text-sm text-slate-600">riddlesTranslateQuestionsAnswerUser</p>
                                    <p class="text-sm text-slate-600">riddlesTranslateSuccess</p>
                                    <p class="text-sm text-slate-600">riddlesTranslatetCooldown</p>
                                    <p class="text-sm text-slate-600">riddlesTranslateGeneralRating</p>
                                </div>

                                <div class="card-game rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <img class="h-14 w-14" src="https://www.language.onllyons.com/ru/ru-en/dist/images/other/cards-icon/quiz-game-change-eyes.webp" alt="Переведите аудио" />
                                    <h3 class="mt-3 text-lg font-semibold">
                                        <a class="hover:underline" href="/play/riddles-pick/" target="_blank">Переведите аудио</a>
                                    </h3>
                                    <p class="mt-3 text-sm text-slate-600">riddlesPickQuestions</p>
                                    <p class="text-sm text-slate-600">riddlesPickSuccess</p>
                                    <p class="text-sm text-slate-600">riddlesPickGeneralRating</p>
                                    <p class="text-sm text-slate-600">riddlesPickCooldown</p>
                                </div>

                                <div class="card-game rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <img class="h-14 w-14" src="https://www.language.onllyons.com/ru/ru-en/dist/images/other/cards-icon/quiz-game-true-false.webp" alt="Верно - Не верно" />
                                    <h3 class="mt-3 text-lg font-semibold">
                                        <a class="hover:underline" href="/play/riddles-true-false/" target="_blank">Верно - Не верно</a>
                                    </h3>
                                    <p class="mt-3 text-sm text-slate-600">riddlesTrueFalseQuestions</p>
                                    <p class="text-sm text-slate-600">riddlesTrueFalseSuccess</p>
                                    <p class="text-sm text-slate-600">riddlesTrueFalseGeneralRating</p>
                                    <p class="text-sm text-slate-600">riddlesTrueFalseCooldown</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
