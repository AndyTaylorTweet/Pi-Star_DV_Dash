<?php if ($enableDarkMode == 1) { ?>
@media (prefers-color-scheme: dark) {
	body {
		background-color: #<?php echo $backgroundPageDark; ?> !important;
        color: #fff;
    }

	.header,
	.footer {
        color: #<?php echo $textBanners; ?>;
    }

    .header,
    .footer,
	input.toggle-round-flat:checked + label,
	input.toggle-round-flat:checked + label:after {
		background-color: #<?php echo $backgroundBannersDark; ?> !important;
	}

    h1 {
        text-shadow: 2px 2px #<?php echo $bannerDropShaddowsDark; ?>;
    }

	/* Various containers */
	.content, .contentwide, .contentwide h2 {
		color: #<?php echo $textContentDark; ?>;
	}
	.content, .contentwide, .container {
		background: #<?php echo $backgroundContentDark; ?>;
	}

	/* Tables */
	table {
		background: #000 !important;
		border-color: #fff;
		color: #fff;
	}
	table tr:nth-child(odd) {
		background: #<?php echo $tableRowOddBgDark; ?>;
		color: #fff;
	}
	table tr:nth-child(even) {
		background: #<?php echo $tableRowEvenBgDark; ?>;
		color: #fff;
	}
	table th {
        text-shadow: 1px 1px #<?php echo $tableHeadDropShaddowDark; ?>;
        background: #<?php echo $backgroundBannersDark; ?>;
    }
    table td {
		color: #fff;
	}
	table td a {
		color: #fff !important;
	}
	td[style="background:#f33;"] {
		/* Black text on high-loss signals -- no CSS class to target */
		color: #000;
	}
	.nav td[style="background: #ffffff;"] {
		/* The tables in the left-hand column are explicitly turned white.
			Let's correct that. */
		background-color: #555 !important;
	}
	td[style*="color:#030;"] {
		/* Enabled modes and networks. */
		color: #cfc !important;
	}
	td[style*="background: #b55"] {
		/* Inactive services */
		background: #800 !important;
	}
	td[style*="background: #1b1"] {
		/* Inactive services */
		background: #0a0 !important;
	}

	/* Inputs and form fields */
	.current, li.option {
		color: #000;
	}
	input, select {
		background-color: #c8c8c8;
	}
	.nice-select, .nice-select-dropdown {
		background-color: #c8c8c8 !important;
	}
	textarea {
		background-color: #212121;
		color: #fff;
	}

	/* Toggle switches */
	input.toggle-round-flat + label {
		/* Toggle switch border */
		background-color: #8a8a8a;
	}
	tr:nth-child(odd) input.toggle-round-flat + label {
		/* Make them stand out more on odd-colored rows. */
		background: #bbb;
	}
	input.toggle-round-flat + label:before {
		/* Toggle switch background */
		background-color: #343434;
	}
	input.toggle-round-flat + label:after {
		/* Inactive toggle switch color */
		background-color: #8a8a8a;
	}
}
<?php } ?>
