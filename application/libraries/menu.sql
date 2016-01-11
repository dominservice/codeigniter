-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: dominservice.nazwa.pl:3306
-- Czas generowania: 11 Sty 2016, 12:01
-- Wersja serwera: 5.5.43-MariaDB-log
-- Wersja PHP: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Baza danych: `dominservice_3`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dso_menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `label` varchar(50) NOT NULL DEFAULT '',
  `link` varchar(100) NOT NULL DEFAULT 'javascript:;',
  `parent` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) DEFAULT NULL,
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL DEFAULT '',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL DEFAULT '',
  `localization` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
  `permissions` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `color` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin2;

--
-- Zrzut danych tabeli `menu`
--

INSERT INTO `menu` (`id_menu`, `label`, `link`, `parent`, `sort`, `keywords`, `description`, `localization`, `permissions`, `icon`, `color`) VALUES
(1, 'home', '', 0, 1, '', '', 'leftbar', '0', 'home', ''),
(2, 'Articles', 'articles', 0, 2, '', 'description in tooltip', 'leftbar', '1', 'alias fa-dashboard', ''),
(3, 'Article 1', 'article_1', 2, 1, '', 'description in tooltip', 'leftbar', '1', 'alias fa-dashboard', ''),
(4, 'Articles 2', 'article_2', 2, 2, '', 'description in tooltip', 'leftbar', '1', 'alias fa-dashboard', ''),
(5, 'Articles 3', 'article_3', 2, 3, '', 'description in tooltip', 'leftbar', '1', 'alias fa-dashboard', ''),
(6, 'Articles 4', 'article_4', 2, 4, '', 'description in tooltip', 'leftbar', '1', 'alias fa-dashboard', ''),

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `menu_html`
--

CREATE TABLE `menu_html` (
  `id_dso_menu_html` int(11) NOT NULL,
  `name_dso_menu_html` varchar(255) NOT NULL,
  `value_dso_menu_html` text NOT NULL,
  `date_upd_dso_menu_html` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin2;

--
-- Zrzut danych tabeli `menu_html`
--

INSERT INTO `menu_html` (`id_dso_menu_html`, `name_dso_menu_html`, `value_dso_menu_html`, `date_upd_dso_menu_html`) VALUES
(4, 'link', '<li class="{a} tooltips" data-container="body" data-placement="right" data-html="true" data-original-title="{description}"><a href="{link}" ><i class="fa fa-{icon}"></i><span class="{color}">{label}</span><span class="arrow "></span></a><li>\n', '2016-01-05 09:00:00'),
(5, 'link2', '<li class="{a} tooltips" data-container="body" data-placement="right" data-html="true" data-original-title="{description}"><a href="{link}" ><i class="fa fa-{icon}"></i><span class="{color}">{label}</span><span class="arrow "></span></a>\n', '2016-01-05 09:00:00'),
(6, 'submenu', '<ul class="sub-menu">\n', '0000-00-00 00:00:00'),
(7, 'submenu2', '</ul>\n', '0000-00-00 00:00:00'),
(8, 'link3', '</li>\n', '0000-00-00 00:00:00');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indexes for table `menu_html`
--
ALTER TABLE `menu_html`
  ADD PRIMARY KEY (`id_dso_menu_html`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=55;
--
-- AUTO_INCREMENT dla tabeli `menu_html`
--
ALTER TABLE `menu_html`
  MODIFY `id_dso_menu_html` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
