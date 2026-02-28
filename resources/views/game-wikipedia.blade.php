<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Game Wikipedia" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Game Wikipedia" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Game Wikipedia</h1>
                        <p class="mt-2 text-sm text-slate-600">Knowledge base for games logic and references.</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 mb-0 p-4 relative rounded-md shadow-sm text-black uk-alert mt-4" uk-alert>
                        <a class="uk-alert-close absolute right-0 top-0 m-5 uk-icon uk-close" uk-close></a>

                        <h3 class="text-lg font-semibold text-black">Notice</h3>
                        <p class="text-black mt-1">
                            <b>MySQL: <span>a_game_text</span></b><br>
                            <b>URL: <a class="text-sky-600" href="https://www.language.onllyons.com/play/chose/" target="_blank">https://www.language.onllyons.com/play/chose/</a></b><br>
                        </p>

                        <ul style="list-style: auto; margin-left: 1.5rem;">
                            <li>Вопросы показаны в диапазоне рейтинга, соответствующему рейтингу пользователя с добавлением и вычитанием определенного значения. Например, если рейтинг пользователя равен 1000, то диапазон его вопросов будет составлять от 500 до 1250.<br><br>Вот пример: если у пользователя рейтинг 1000, то ему будут представлены случайные вопросы из каждой категории, но только те, которые имеют рейтинг в диапазоне от 500 до 1250. Например, если в категории "synonyms" есть вопросы с рейтингом 600, 900 и 1300, то пользователю будут представлены только вопросы с рейтингами 600 и 900.</li>
                            <li>Никакой пользователь не может упасть ниже рейтинга 300.</li>
                            <li>Если после определенного рейтинга закончатся вопросы, то рейтинг уменьшится. Он уменьшается всегда на 100 баллов.<br>Таким образом, диапазон обычно составляет минус 500 баллов от рейтинга пользователя плюс 250 баллов от рейтинга пользователя. Если же вопросы закончились, то рейтинг будет рассчитан по формуле: минус 600 рейтинг пользователя плюс 250. Это продолжается до 300 рейтинга.<br><br>Пример для рейтинга 1000:<br>1000 - 500 | 1000 + 250 =&gt; диапазон: 500 - 1250;<br>1000 - 600 | 1000 + 250 =&gt; диапазон: 400 - 1250;<br>и после этого каждый раз падает на 100 баллов<br>...<br>...</li>
                        </ul>

                        <b>Добавление бонуса для пользователей, которые решают вопросы быстрее, чем время, указанное в базе данных (cell: deducted_time):</b>
                        <ul style="list-style: auto; margin-left: 1.5rem;">
                            <li>Если пользователь решает вопросы быстрее, чем за 33% от целевого времени (cell: deducted_time), то он получает бонус +3.</li>
                            <li>Если пользователь решает вопросы быстрее, чем за 66% от целевого времени (cell: deducted_time), то он получает бонус +2.</li>
                            <li>Если же еще не прошло целевое время (cell: deducted_time), пользователь получает бонус +1.</li>
                        </ul>

                        <br>

                        <ul style="list-style: auto; margin-left: 1.5rem;">
                            <li><code>added_points</code>: Здесь сохраняются баллы, которые добавляются за правильный ответ на вопросы.</li>
                            <li><code>deducted_points</code>: Здесь сохраняются баллы, которые вычитаются за неправильный ответ на вопросы.</li>
                        </ul>

                        <br>

                        <b>Как работает EChart:</b>
                        <ul style="list-style: auto; margin-left: 1.5rem;">
                            <li>Верхний и нижний порог составляет 30 единиц измерение, если линия превышает этот порог, график расширяется с двух сторон на + 30 единиц измерение.</li>
                            <li>Длина допуска равна 20 единиц измерение, если количество достигает 20 единиц измерение, происходит смещение вперед на 5 единиц измерение. Теперь график может вместить еще 5 единиц измерение, но при этом прошлые 5 будут обрезаны.</li>
                            <li>При загрузке страницы мы запоминаем временную метку unix в js и при обращении к серверу мы передаем это время, что бы из таблицы a_game_rating брались только те вопросы время завершения которых было таким же самым или большем чем мы передаем.</li>
                        </ul>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
