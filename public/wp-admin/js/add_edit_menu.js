jQuery(document).ready(function () {
	/* =============== DEMO =============== */
	// menu items
	var arrayjson = [
		{
			href: "http://home.com",
			icon: "fas fa-home",
			text: "Home",
			target: "_top",
			title: "My Home",
		},
		{
			icon: "fas fa-chart-bar",
			text: "Opcion2",
		},
		{
			icon: "fas fa-bell",
			text: "Opcion3",
		},
		{
			icon: "fas fa-crop",
			text: "Opcion4",
		},
		{
			icon: "fas fa-flask",
			text: "Opcion5",
		},
		{
			icon: "fas fa-map-marker",
			text: "Opcion6",
		},
		{
			icon: "fas fa-search",
			text: "Opcion7",
			children: [
				{
					icon: "fas fa-plug",
					text: "Opcion7-1",
					children: [
						{
							icon: "fas fa-filter",
							text: "Opcion7-1-1",
						},
					],
				},
			],
		},
	];
	// icon picker options
	var iconPickerOptions = {
		searchText: "Buscar...",
		labelHeader: "{0}/{1}",
	};
	// sortable list options
	var sortableListOptions = {
		placeholderCss: {
			"background-color": "#cccccc",
		},
	};

	var editor = new MenuEditor("myEditor", {
		listOptions: sortableListOptions,
		iconPicker: iconPickerOptions,
	});
	editor.setForm(jQuery("#frmEdit"));
	editor.setUpdateButton(jQuery("#btnUpdate"));
	jQuery("#btnReload").on("click", function () {
		editor.setData(arrayjson);
	});

	jQuery("#btnOutput").on("click", function () {
		var str = editor.getString();
		jQuery("#out").text(str);
	});

	jQuery("#btnUpdate").click(function () {
		editor.update();
	});

	jQuery("#btnAdd").click(function () {
		editor.add();
	});
	/* ====================================== */

	/** PAGE ELEMENTS **/
	jQuery('[data-toggle="tooltip"]').tooltip();
	jQuery.getJSON(
		"https://api.github.com/repos/davicotico/jQuery-Menu-Editor",
		function (data) {
			jQuery("#btnStars").html(data.stargazers_count);
			jQuery("#btnForks").html(data.forks_count);
		}
	);
});
