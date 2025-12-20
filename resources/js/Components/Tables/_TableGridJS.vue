<script setup>
import { onMounted } from 'vue';
import { Grid, html } from 'gridjs';
import 'gridjs/dist/theme/mermaid.css';

const data = [
    [1, "Janathan", "jonathan@example.com", "Senior Implementation Architect", "Koelpin - Goldner", "Vanuatu"],
    [2, "Harold", "harold@example.com", "Forward Creative Coordinator", "Feeney, Langworth and Tremblay", "Niger"],
    [3, "Shannan", "shannon@example.com", "Legacy Functionality Associate", "Streich Group", "Niue"],
    [4, "Robert", "robert@example.com", "Product Accounts Technician", "Ebert, Schamberger and Johnston", "Mexico"],
    [5, "Noel", "noel@example.com", "Customer Data Director", "Raynor, Rolfson and Daugherty", "Qatar"],
];

onMounted(() => {
    const target = document.getElementById("wrapper");

    const grid = new Grid({
        className: {
            table: 'table align-middle table-nowrap',    // classe para a tabela
            thead: 'table-light text-muted',             // cabeçalho
            tbody: 'list form-check-all',                // corpo da tabela
            footer: '',                                  // rodapé (paginações, etc)
            pagination: '',                              // paginação
            search: '',                                  // input de busca
            sort: '',                                    // ícones de ordenação
            th: '',                                      // cabeçalhos das colunas
            td: '',                                      // células da tabela
            loading: '',                                 // estado de carregamento
            notfound: '',                                // quando não encontra resultados
            container: ''                                // wrapper geral
        },
        columns: [
            { name: "ID", sort: true },
            { name: "Name", sort: true },
            { name: "Email", sort: true },
            { name: "Position", sort: true },
            { name: "Company", sort: true },
            { name: "Country", sort: true },
            {
                name: "Action",
                formatter: (_, row) =>
                    html(`<a href="#" scope="col">Details</a>`)
            }
        ],
        data: data,
        pagination: {
            limit: 5,
            summary: true,
            previous: 'Anterior',
            next: 'Próximo',
            showing: 'Exibindo',
            results: () => 'registros'
        },
        search: true,
        sort: true,
        language: {
            search: {
                placeholder: 'Buscar usuário...'
            },
            loading: 'Carregando...',
            noRecordsFound: ""
        }
    });

    grid.render(target);

    // Customiza barra de busca e botões extras
    const search = document.querySelector('.gridjs-search');
    const parent = search?.parentNode;



    if (search && parent) {
        // Cria o wrapper principal com layout responsivo
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center card-header px-0 border-0 gap-2';

        // Insere o wrapper no lugar do original
        parent.insertBefore(wrapper, search);

        // Botões
        const buttonGroup = document.createElement('div');
        buttonGroup.className = 'hstack gap-2 border-0 order-0 order-sm-1';
        buttonGroup.innerHTML = `
            <a class="btn btn-md btn-info" href="#offcanvasExample" role="button">
            <i class="ri-filter-3-line align-bottom me-1"></i>
            <span class="d-none d-sm-inline">Filters</span>
            </a>
            <a class="btn btn-md btn-success add-btn" type="button" id="create-btn">
            <i class="ri-add-line align-bottom me-1"></i>
            <span class="d-none d-sm-inline">Add Leads</span>
            </a>
        `;

        // Aplica as classes de ordenação ao campo de busca original
        search.classList.add('order-1', 'order-sm-0');

        // Adiciona botões e busca ao wrapper
        wrapper.appendChild(buttonGroup);
        wrapper.appendChild(search);
    }


    // Observador para .gridjs-notfound
    const observer = new MutationObserver(() => {
        const notFoundElement = document.querySelector('.gridjs-notfound');
        if (notFoundElement && !notFoundElement.dataset.customized) {
            notFoundElement.innerHTML = `
            <div class="noresult">
            <div class="text-center">
                <div class="avatar-xl" colors="primary:#121331,secondary:#08a88a"
                    style="width: 75px; height: 75px; overflow: hidden; margin: 0px auto;">
                <svg xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 500 500"
                    width="500"
                    height="500"
                    preserveAspectRatio="xMidYMid meet"
                    style="width: 100%; height: 100%; transform: translate3d(0px, 0px, 0px); content-visibility: visible;">
                    <defs>
                    <clipPath id="__lottie_element_2">
                        <rect width="500" height="500" x="0" y="0"></rect>
                    </clipPath>
                    </defs>
                    <g clip-path="url(#__lottie_element_2)">
                    <g transform="matrix(3.4993817806243896,0.0657772645354271,-0.0657772645354271,3.4993817806243896,258.859375,250.55609130859375)"
                        opacity="1"
                        style="display: block;">
                        <g opacity="1" transform="matrix(1,0,0,1,0,0)">
                        <path stroke-linecap="round"
                                stroke-linejoin="round"
                                fill-opacity="0"
                                stroke="rgb(18,19,49)"
                                stroke-opacity="1"
                                stroke-width="3.5"
                                d=" M20.799999237060547,-37.79100036621094 C36.99599838256836,-21.594999313354492 36.99599838256836,4.664999961853027 20.799999237060547,20.861000061035156 C4.604000091552734,37.05699920654297 -21.656999588012695,37.05699920654297 -37.85300064086914,20.861000061035156 C-54.04899978637695,4.664999961853027 -54.04899978637695,-21.594999313354492 -37.85300064086914,-37.79100036621094 C-21.656999588012695,-53.98699951171875 4.604000091552734,-53.98699951171875 20.799999237060547,-37.79100036621094z">
                        </path>
                        </g>
                    </g>
                    <g transform="matrix(3.4993817806243896,0.0657772645354271,-0.0657772645354271,3.4993817806243896,258.859375,250.55609130859375)"
                        opacity="1"
                        style="display: block;">
                        <g opacity="1" transform="matrix(1,0,0,1,0,0)">
                        <path stroke-linecap="round"
                                stroke-linejoin="round"
                                fill-opacity="0"
                                stroke="rgb(8,168,138)"
                                stroke-opacity="1"
                                stroke-width="3.64"
                                d=" M-39.94300079345703,-20.42799949645996 C-38.32600021362305,-24.739999771118164 -35.78300094604492,-28.784000396728516 -32.31399917602539,-32.25299835205078 C-28.371999740600586,-36.19499969482422 -23.68600082397461,-38.94200134277344 -18.708999633789062,-40.492000579833984">
                        </path>
                        </g>
                    </g>
                    <g transform="matrix(3.4993817806243896,0.0657772645354271,-0.0657772645354271,3.4993817806243896,258.859375,250.55609130859375)"
                        opacity="1"
                        style="display: block;">
                        <g opacity="1" transform="matrix(1,0,0,1,0,0)">
                        <path stroke-linecap="round"
                                stroke-linejoin="round"
                                fill-opacity="0"
                                stroke="rgb(18,19,49)"
                                stroke-opacity="1"
                                stroke-width="4.2"
                                d=" M21.486000061035156,21.427000045776367 C21.486000061035156,21.427000045776367 46.641998291015625,46.152000427246094 46.641998291015625,46.152000427246094">
                        </path>
                        </g>
                    </g>
                    <g class="com" style="display: none;">
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                        <g><path></path></g>
                    </g>
                    </g>
                </svg>
                </div>
                <h5 class="mt-2">Sorry! No Result Found</h5>
                <p class="text-muted mb-0">
                We've searched more than 150+ Orders. We did not find any orders for your search.
                </p>
            </div>
            </div>

      `;
            notFoundElement.dataset.customized = 'true';
        }
    });

    observer.observe(document.getElementById("wrapper"), {
        childList: true,
        subtree: true,
    });
});
</script>

<template>
    <div class="row">
        <div class="card card-body">
            <div class="card-body">
                <div id="wrapper" class="table-responsive table-card"></div>
            </div>
        </div>
    </div>
</template>
