<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Collapsible sidebar using Bootstrap 4</title>

    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">

</head>

<body>


<!-- jQuery CDN - Slim version (=without AJAX) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!-- Popper.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div id="dismiss">
            <i class="fas fa-arrow-left"></i>
        </div>

        <div class="sidebar-header">
            <h3>Bootstrap Sidebar</h3>
        </div>

        <ul class="list-unstyled components">
            <p>Dummy Heading</p>
            <li class="active">
                <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false">Home</a>
                <ul class="collapse list-unstyled" id="homeSubmenu">
                    <li>
                        <a href="#">Home 1</a>
                    </li>
                    <li>
                        <a href="#">Home 2</a>
                    </li>
                    <li>
                        <a href="#">Home 3</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#">About</a>
                <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false">Pages</a>
                <ul class="collapse list-unstyled" id="pageSubmenu">
                    <li>
                        <a href="#">Page 1</a>
                    </li>
                    <li>
                        <a href="#">Page 2</a>
                    </li>
                    <li>
                        <a href="#">Page 3</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#">Portfolio</a>
            </li>
            <li>
                <a href="#">Contact</a>
            </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content">

        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">

                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="fas fa-align-left"></i>
                    <span>Переключатель меню</span>
                </button>

            </div>
        </nav>

        <h2>SideBar на Bootstrap 4</h2>
        <p>Лишь непосредственные участники технического прогресса, превозмогая сложившуюся непростую экономическую ситуацию, разоблачены. Высокое качество позиционных исследований создает предпосылки для форм воздействия. Сложно сказать, почему сторонники тоталитаризма в науке объявлены нарушающими общечеловеческие нормы этики и морали!</p>

        <p>С учетом сложившейся международной обстановки, семантический разбор внешних противодействий представляет собой интересный эксперимент проверки глубокомысленных рассуждений. Сложно сказать, почему ключевые особенности структуры проекта подвергнуты целой серии независимых исследований. Противоположная точка зрения подразумевает, что тщательные исследования конкурентов лишь добавляют фракционных разногласий и обнародованы.</p>

        <div class="line"></div>

        <h2>Заголовок 2</h2>
        <p>Для современного мира современная методология разработки способствует подготовке и реализации новых предложений. Таким образом, высококачественный прототип будущего проекта создает необходимость включения в производственный план целого ряда внеочередных мероприятий с учетом комплекса вывода текущих активов. Равным образом, высокое качество позиционных исследований позволяет выполнить важные задания по разработке новых предложений. С другой стороны, повышение уровня гражданского сознания прекрасно подходит для реализации анализа существующих паттернов поведения. Противоположная точка зрения подразумевает, что сделанные на базе интернет-аналитики выводы и по сей день остаются уделом либералов, которые жаждут быть преданы социально-демократической анафеме. Высокий уровень вовлечения представителей целевой аудитории является четким доказательством простого факта: выбранный нами инновационный путь представляет собой интересный эксперимент проверки системы обучения кадров, соответствующей насущным потребностям.</p>

        <p>Сложно сказать, почему акционеры крупнейших компаний формируют глобальную экономическую сеть и при этом - описаны максимально подробно. Внезапно, явные признаки победы институциализации, вне зависимости от их уровня, должны быть описаны максимально подробно. Прежде всего, повышение уровня гражданского сознания прекрасно подходит для реализации как самодостаточных, так и внешне зависимых концептуальных решений. Повседневная практика показывает, что сплоченность команды профессионалов, в своем классическом представлении, допускает внедрение новых предложений.</p>

        <p>С учетом сложившейся международной обстановки, постоянный количественный рост и сфера нашей активности позволяет выполнить важные задания по разработке кластеризации усилий. Банальные, но неопровержимые выводы, а также стремящиеся вытеснить традиционное производство, нанотехнологии неоднозначны и будут превращены в посмешище, хотя само их существование приносит несомненную пользу обществу.</p>

        <div class="line"></div>

        <h2>Заголовок 3</h2>
        <p>Приятно, граждане, наблюдать, как тщательные исследования конкурентов подвергнуты целой серии независимых исследований. Следует отметить, что экономическая повестка сегодняшнего дня однозначно определяет каждого участника как способного принимать собственные решения касаемо приоретизации разума над эмоциями. В своем стремлении улучшить пользовательский опыт мы упускаем, что предприниматели в сети интернет функционально разнесены на независимые элементы!</p>


    </div>
</div>

<div class="overlay"></div>

<style>
    #sidebar {
    width: 250px;
    position: fixed;
    top: 0;
    left: -250px;
    height: 100vh;
    z-index: 999;
    background: #7386D5;
    color: #fff;
    transition: all 0.3s;
    overflow-y: scroll;
    box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.2);
}
#sidebar.active {
    left: 0;
}


.overlay {
    display: none;
    position: fixed;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    z-index: 998;
    opacity: 0;
    transition: all 0.5s ease-in-out;
    top: 0;
    left: 0;
}
.overlay.active {
    display: block;
    opacity: 1;
}

#sidebar .sidebar-header {
    padding: 20px;
    background: #6d7fcc;
}


a[data-toggle="collapse"] {
    position: relative;
}

.dropdown-toggle::after {
    display: block;
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
}
</style>

<script>
$(document).ready(function () {
    $('#dismiss, .overlay').on('click', function () {
        $('#sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').addClass('active');
        $('.overlay').addClass('active');
        $('.collapse.in').toggleClass('in');
        $('a[aria-expanded=true]').attr('aria-expanded', 'false');
    });
});
</script>

</body>

</html>