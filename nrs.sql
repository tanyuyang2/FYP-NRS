-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2026 at 05:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nrs`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_token`
--

CREATE TABLE `account_token` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_token`
--

INSERT INTO `account_token` (`token_id`, `user_id`, `token_hash`, `expire_at`, `created_at`) VALUES
(5, 2, 'f72b0bce379b01bf512c229ca6f28ec5052a2d87e097aefea8f4f8a0f058284e', '2026-10-04 16:30:20', '2026-10-04 15:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `article`
--

CREATE TABLE `article` (
  `article_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `summary` text NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `trending_score` int(11) NOT NULL DEFAULT 0,
  `score_calculated_at` datetime DEFAULT NULL,
  `background_cosmetic_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article`
--

INSERT INTO `article` (`article_id`, `title`, `content`, `summary`, `thumbnail`, `author_id`, `status`, `published_at`, `updated_at`, `category_id`, `trending_score`, `score_calculated_at`, `background_cosmetic_id`) VALUES
(1, 'Nintendo Reveals a New Adventure Coming to Switch', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Nintendo has revealed a new adventure for Switch players, featuring a colorful world, new characters, and a focus on exploration. The announcement has already sparked plenty of discussion among fans who are looking forward to seeing how the new title expands on familiar Nintendo gameplay.', 'https://placehold.co/800x450/png?text=Gaming+News+1', 1, 1, '2026-09-18 04:30:00', NULL, 1, 1, '2026-10-07 22:49:31', NULL),
(2, 'The Next Nintendo Direct Has Fans Guessing What Comes Next', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Nintendo has announced another presentation, giving players a chance to learn more about upcoming games and updates. With several titles still generating interest among the community, fans are already sharing their predictions about what could appear during the presentation.', 'https://placehold.co/800x450/png?text=Gaming+News+2', 2, 1, '2026-09-17 19:30:00', NULL, 1, 1, '2026-10-11 01:41:29', NULL),
(3, 'Mario Kart Gets a Fresh Update for Switch Players', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new Mario Kart update brings additional content and several small improvements to the racing experience. Players can look forward to new ways to customize their races while continuing to compete with friends both locally and online.', 'https://placehold.co/800x450/png?text=Gaming+News+3', 3, 1, '2026-09-17 10:30:00', NULL, 1, 0, NULL, NULL),
(4, 'Pokémon Fans Discover a New Feature in the Latest Update', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'The latest Pokémon update introduces a new feature designed to make exploration and collecting more engaging. The addition gives returning players another reason to revisit the game while providing newcomers with more ways to interact with the world.', 'https://placehold.co/800x450/png?text=Gaming+News+4', 4, 4, '2026-09-17 01:30:00', NULL, 1, 0, NULL, NULL),
(5, 'Kirby Returns in a Colorful New Platforming Adventure', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Kirby is back in a new platforming adventure filled with colorful environments, familiar abilities, and hidden secrets. The new game aims to offer an accessible experience while still giving longtime fans plenty to explore across its stages.', 'https://placehold.co/800x450/png?text=Gaming+News+5', 5, 1, '2026-09-17 02:30:00', NULL, 1, 1, '2026-10-07 20:24:21', NULL),
(6, 'Nintendo Announces New Features for Its Online Service', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Nintendo has announced several new additions and improvements for its online service. The changes are focused on making the experience more convenient for players and giving subscribers more ways to interact with their games and friends.', 'https://placehold.co/800x450/png?text=Gaming+News+6', 6, 1, '2026-09-16 17:30:00', NULL, 1, 0, NULL, NULL),
(7, 'Fans Share Their Most-Wanted Nintendo Remakes', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Nintendo fans are once again discussing which classic games deserve to return on modern hardware. Community discussions have highlighted a wide range of older titles, with players hoping that future remakes can preserve the original experience while introducing updated visuals and quality-of-life improvements.', 'https://placehold.co/800x450/png?text=Gaming+News+7', 7, 1, '2026-09-16 08:30:00', NULL, 1, 0, NULL, NULL),
(8, 'The Latest Switch Update Adds Several Quality-of-Life Changes', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new system update for Switch introduces several smaller changes aimed at improving the everyday experience. While the update does not completely transform the console, the improvements should make common tasks more convenient for players.', 'https://placehold.co/800x450/png?text=Gaming+News+8', 8, 1, '2026-09-15 23:30:00', NULL, 1, 1, '2026-10-07 20:24:21', NULL),
(9, 'A New Fantasy RPG Is Coming to Nintendo Switch', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new fantasy role-playing game is making its way to Nintendo Switch, bringing players into a world filled with exploration, mysterious characters, and challenging encounters. The title has attracted attention for its colorful presentation and adventure-focused gameplay.', 'https://placehold.co/800x450/png?text=Gaming+News+9', 9, 1, '2026-09-15 14:30:00', NULL, 1, 0, NULL, NULL),
(10, 'Nintendo Fans Get a First Look at an Upcoming Adventure', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Players have received their first look at an upcoming Nintendo adventure, offering a glimpse at its world, characters, and gameplay. Although details remain limited, the reveal has given fans plenty to discuss while they wait for more information.', 'https://placehold.co/800x450/png?text=Gaming+News+10', 10, 1, '2026-09-15 15:30:00', NULL, 1, 1, '2026-10-07 20:24:21', NULL),
(11, 'PlayStation Reveals a New Story-Driven Adventure', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'PlayStation has unveiled a new story-driven adventure that places a strong focus on exploration, characters, and cinematic moments. The first details have given players an early look at what could become one of the platform\'s upcoming narrative-focused releases.', 'https://placehold.co/800x450/png?text=Gaming+News+11', 11, 1, '2026-09-15 06:30:00', NULL, 2, 0, NULL, NULL),
(12, 'New PlayStation Update Introduces Several Quality-of-Life Improvements', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new PlayStation system update introduces a collection of quality-of-life improvements for players. The changes focus on making everyday features easier to use while addressing several smaller issues reported by the community.', 'https://placehold.co/800x450/png?text=Gaming+News+12', 12, 4, '2026-09-18 21:30:00', NULL, 2, 2, '2026-10-07 22:49:31', NULL),
(13, 'Fans React to the Latest PlayStation Showcase', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'The latest PlayStation showcase gave players a look at several upcoming games across different genres. New trailers and announcements have sparked conversations among the community, with fans discussing which titles they are most interested in following.', 'https://placehold.co/800x450/png?text=Gaming+News+13', 13, 4, '2026-09-14 12:30:00', NULL, 2, 0, NULL, NULL),
(14, 'A New Fantasy RPG Is Coming to PlayStation', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new fantasy RPG is set to arrive on PlayStation, offering players a large world to explore alongside a story built around memorable characters and challenging encounters. The project is still being revealed gradually, with more details expected in future announcements.', 'https://placehold.co/800x450/png?text=Gaming+News+14', 14, 1, '2026-09-14 03:30:00', NULL, 2, 0, NULL, NULL),
(15, 'PlayStation Players Discover a Hidden Feature in the Latest Update', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Players have discovered an easily overlooked feature included in a recent PlayStation update. The addition is relatively small, but it provides another useful option for managing games and interacting with the console.', 'https://placehold.co/800x450/png?text=Gaming+News+15', 15, 1, '2026-09-16 04:30:00', NULL, 2, 0, NULL, NULL),
(16, 'The Year\'s Biggest PlayStation Releases Continue to Take Shape', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Several major PlayStation releases are continuing to take shape as developers reveal more information about their upcoming projects. New trailers, gameplay details, and announcements are giving players a clearer picture of what is ahead.', 'https://placehold.co/800x450/png?text=Gaming+News+16', 16, 1, '2026-09-13 19:30:00', NULL, 2, 0, NULL, NULL),
(17, 'A New Co-op Adventure Is Coming to PlayStation', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new cooperative adventure is bringing two or more players together for a journey filled with puzzles, exploration, and combat. The game is designed around teamwork, giving players reasons to communicate and work together throughout the experience.', 'https://placehold.co/800x450/png?text=Gaming+News+17', 17, 1, '2026-09-13 10:30:00', NULL, 2, 0, NULL, NULL),
(18, 'Xbox Players Get a New Open-World Adventure to Explore', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Xbox players are getting a new open-world adventure featuring a large environment, optional activities, and a story built around exploration. The game gives players plenty of freedom to decide how they want to approach its world.', 'https://placehold.co/800x450/png?text=Gaming+News+18', 18, 1, '2026-09-13 01:30:00', NULL, 3, 0, NULL, NULL),
(19, 'Xbox Game Pass Adds More Games This Month', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Xbox Game Pass is adding several new games to its library, giving subscribers more opportunities to discover different genres and experiences. The latest additions include a mix of larger releases and smaller titles that are worth checking out.', 'https://placehold.co/800x450/png?text=Gaming+News+19', 19, 1, '2026-09-12 16:30:00', NULL, 3, 0, NULL, NULL),
(20, 'New Xbox Update Improves the Gaming Experience', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new Xbox update introduces several improvements focused on performance, navigation, and general usability. The changes are designed to make the console experience smoother while addressing some of the smaller frustrations players have encountered.', 'https://placehold.co/800x450/png?text=Gaming+News+20', 20, 1, '2026-09-12 17:30:00', NULL, 3, 0, NULL, NULL),
(21, 'A New Co-op Adventure Is Coming to Xbox', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Xbox players will soon have another cooperative adventure to play with friends. The upcoming title combines exploration and teamwork, with challenges that encourage players to communicate and coordinate their actions.', 'https://placehold.co/800x450/png?text=Gaming+News+21', 1, 1, '2026-09-12 08:30:00', NULL, 3, 1, '2026-10-07 22:49:31', NULL),
(22, 'Xbox Fans Get Their First Look at an Upcoming RPG', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Xbox fans have received an early look at a new role-playing game currently in development. The reveal showcases its world, combat system, and characters while leaving plenty of details for future announcements.', 'https://placehold.co/800x450/png?text=Gaming+News+22', 2, 1, '2026-09-18 18:30:00', NULL, 3, 1, '2026-10-08 23:32:24', NULL),
(23, 'The Latest Xbox Features Give Players More Ways to Play', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Several new Xbox features are giving players more flexibility in how they manage and enjoy their games. The additions focus on convenience and accessibility, continuing the platform\'s steady stream of system improvements.', 'https://placehold.co/800x450/png?text=Gaming+News+23', 3, 1, '2026-09-11 14:30:00', NULL, 3, 0, NULL, NULL),
(24, 'The Latest PC Gaming Update Brings Major Performance Improvements', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new PC gaming update focuses on performance and stability improvements across a range of systems. Players can expect smoother gameplay in supported titles, along with fixes for several issues that have affected the experience.', 'https://placehold.co/800x450/png?text=Gaming+News+24', 4, 1, '2026-09-11 05:30:00', NULL, 4, 0, NULL, NULL),
(25, 'Indie Developers Show Off Their Most Ambitious Projects Yet', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Independent developers are continuing to experiment with creative ideas, distinctive art styles, and unusual gameplay systems. Several upcoming indie projects are attracting attention for taking familiar genres in new directions.', 'https://placehold.co/800x450/png?text=Gaming+News+25', 5, 1, '2026-09-11 06:30:00', NULL, 4, 0, NULL, NULL),
(26, 'PC Players Are Getting More Ways to Customize Their Games', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'PC players continue to benefit from new customization tools and community-made content. From visual changes to gameplay modifications, these options give players more freedom to tailor their experiences to their own preferences.', 'https://placehold.co/800x450/png?text=Gaming+News+26', 6, 1, '2026-09-10 21:30:00', NULL, 4, 0, NULL, NULL),
(27, 'A New Survival Game Is Taking the PC Community by Storm', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new survival game has quickly attracted attention from PC players with its combination of exploration, crafting, and resource management. The growing community is already sharing strategies and discovering different ways to survive its challenging world.', 'https://placehold.co/800x450/png?text=Gaming+News+27', 7, 1, '2026-09-10 12:30:00', NULL, 4, 0, NULL, NULL),
(28, 'The Most Interesting PC Games to Watch This Month', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'Several upcoming PC games are worth keeping an eye on this month, ranging from ambitious role-playing games to smaller experimental projects. Each offers something different, giving players plenty of options to add to their watchlists.', 'https://placehold.co/800x450/png?text=Gaming+News+28', 8, 1, '2026-09-10 03:30:00', NULL, 4, 0, NULL, NULL),
(29, 'New Graphics Update Promises Smoother PC Performance', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'A new graphics update is aiming to improve visual quality and performance for supported PC games. The changes are expected to provide a smoother experience while giving players additional options for adjusting their graphics settings.', 'https://placehold.co/800x450/png?text=Gaming+News+29', 9, 1, '2026-09-09 18:30:00', NULL, 4, 1, '2026-10-01 09:22:08', NULL),
(30, 'The Games Everyone Is Talking About This Week', 'The latest gaming news has caught the attention of players and the wider community. More details are expected as developers and publishers share additional information about the project.\r\n\r\nFor now, players can look forward to further announcements and updates as development continues.', 'From major announcements to unexpected community discoveries, the gaming world has had plenty to talk about this week. Several new updates and upcoming releases have captured attention across different platforms and genres.', 'https://placehold.co/800x450/png?text=Gaming+News+30', 10, 1, '2026-09-18 19:30:00', NULL, 1, 1, '2026-10-11 01:41:29', NULL),
(31, 'Nintendo Reveals a New Adventure Coming to Switch', '<p>Nintendo has revealed a brand-new adventure coming to Nintendo Switch, giving players their first look at a colorful world filled with exploration, characters, and hidden secrets. The announcement gives fans an early glimpse at what they can expect from the upcoming title.</p><h3>A New World to Explore</h3><p>The upcoming adventure will take players across a variety of environments, each featuring its own challenges and discoveries. Exploration appears to be an important part of the experience, with players encouraged to search through the environment and uncover optional areas.</p><p>Players can expect:</p><ul><li>Multiple environments to explore</li><li>New characters and encounters</li><li>Different gameplay challenges</li><li>Hidden areas and collectibles</li><li>New mechanics built around exploration</li></ul><h3>Familiar Gameplay, New Ideas</h3><p>Alongside exploration, the game introduces several new gameplay mechanics designed to give players more freedom in how they approach different situations. Some areas will focus on platforming and action, while others encourage players to interact with the environment.</p><blockquote><p>“We wanted to create an adventure that feels familiar to longtime players while giving them something new to discover.”</p></blockquote><h3>More Details Still to Come</h3><p>Nintendo has only revealed a portion of the game so far. Details about the full story, additional characters, and later gameplay systems are still being kept under wraps.</p><p>More information is expected to be shared through future announcements as the release approaches. Until then, fans will have plenty to speculate about as they wait for another look at the upcoming adventure.asd23e</p>', 'Nintendo has revealed a brand-new adventure coming to Switch, introducing players to a colorful world filled with exploration, memorable characters, and hidden secrets. The upcoming title looks set to combine familiar Nintendo charm with new gameplay ideas, giving both longtime fans and newcomers another adventure to look forward to.', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1789720359/FYP-NRS/thumbnails/pkh74cfpu6s4e3jfty1s.png', 1, 1, '2026-09-18 21:46:23', '2026-09-30 14:35:02', 1, 1, '2026-10-11 01:41:29', 10),
(32, 'Trails in the Sky 2nd Chapter demo now available', '<p>Here is an overview of the demo:</p><blockquote><p>This is a free demo version of <i>Trails in the Sky 2nd Chapter</i>, the next chapter of Nihon Falcom’s iconic remake series! The demo picks up immediately after the events of Trails in the Sky 1st Chapter.</p><p>In the demo version, players can experience up through Chapter 1 of the story. Any save data can be carried over to the full version of the game to continue the journey. (Some specifications from the demo may differ from the final version.)</p></blockquote><p><i>Trails in the Sky 2nd Chapter</i> is due out for Play Station 5, Switch 2, Switch, and PC via <a href=\"https://store.steampowered.com/app/4225980/Trails_in_the_Sky_2nd_Chapter/\"><strong>Steam</strong></a> on September 17 worldwide.</p><p>Watch the opening movie and a new teaser trailer below.</p><figure class=\"media\"><div data-oembed-url=\"https://www.youtube.com/watch?v=6NiqCCKaed4\"><div><iframe src=\"https://www.youtube.com/embed/6NiqCCKaed4\" width=\"1280\" height=\"720\" frameborder=\"0\"></iframe></div></div></figure>', 'A demo for Trails in the Sky 2nd Chapter is available now for PlayStation 5, Switch, and PC via Steam, Falcom and GungHo Online Entertainment announced. The opening movie was also released.', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1789722747/FYP-NRS/thumbnails/jeeumndyz5zmqea6rmrk.webp', 1, 1, '2026-09-19 12:48:59', '2026-09-30 14:36:28', 1, 4, '2026-10-11 01:41:29', 10),
(33, 'schedule', '<p>asda</p>', '', NULL, 1, 3, '2026-09-28 17:48:00', '2026-09-30 15:37:32', 1, 2, '2026-10-01 00:52:48', 10),
(34, 'draft', '<p>draft</p>', 'draft', NULL, 1, 3, '2026-09-30 15:37:15', NULL, 1, 1, '2026-09-30 15:37:16', 10),
(35, 'draft', '<p>draft</p>', 'draft', NULL, 1, 0, NULL, NULL, 1, 0, NULL, 10);

-- --------------------------------------------------------

--
-- Table structure for table `article_ai_summary`
--

CREATE TABLE `article_ai_summary` (
  `article_id` int(11) NOT NULL,
  `summary` text NOT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `comment_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_ai_summary`
--

INSERT INTO `article_ai_summary` (`article_id`, `summary`, `generated_at`, `comment_count`) VALUES
(31, 'Article Summary:\nNintendo has announced a colorful new Nintendo Switch adventure focused on exploration, varied environments, characters, hidden areas, collectibles, and new mechanics. The game will combine familiar platforming and action with environmental interaction, though story details, characters, later gameplay systems, and the release timeline remain undisclosed.\n\nComment Summary:\nReaders are interested in the game’s exploration, art direction, soundtrack, secrets, and environmental detail, with many hoping optional areas offer meaningful rewards. Some are curious about character abilities and the balance between story and gameplay, while others are reserving judgment until Nintendo shows a longer gameplay trailer.', '2026-09-19 12:44:22', 16),
(32, 'Article Summary:\nA free demo for Trails in the Sky 2nd Chapter is available, covering the story through Chapter 1 and allowing save data to carry over to the full game. The title launches worldwide on September 17 for PlayStation 5, Switch 2, Switch, and PC via Steam, with an opening movie and teaser trailer released.\n\nComment Summary:\nThe comments express strong enthusiasm, with one reader calling the game “amazing” and another saying they love it.', '2026-10-05 23:27:30', 2);

-- --------------------------------------------------------

--
-- Table structure for table `article_bookmark`
--

CREATE TABLE `article_bookmark` (
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_bookmark`
--

INSERT INTO `article_bookmark` (`article_id`, `user_id`, `created_at`) VALUES
(31, 2, '2026-09-19 12:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `article_reaction`
--

CREATE TABLE `article_reaction` (
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction` tinyint(11) NOT NULL,
  `reacted_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_reaction`
--

INSERT INTO `article_reaction` (`article_id`, `user_id`, `reaction`, `reacted_at`) VALUES
(29, 1, 1, '2026-09-28 17:00:19'),
(31, 2, 1, '2026-09-26 00:25:22'),
(32, 2, 1, '2026-10-05 23:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `article_tag`
--

CREATE TABLE `article_tag` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_tag`
--

INSERT INTO `article_tag` (`article_id`, `tag_id`) VALUES
(32, 4);

-- --------------------------------------------------------

--
-- Table structure for table `article_view`
--

CREATE TABLE `article_view` (
  `article_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `viewer_key` char(64) NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_view`
--

INSERT INTO `article_view` (`article_id`, `user_id`, `viewer_key`, `viewed_at`) VALUES
(1, 2, '0195616cec890a4dc990b9d0d41435fae026900a411f1fe03ba4c2e709701d69', '2026-10-06 00:43:41'),
(1, 1, 'd0b65965d8e78ef60abcd1a277383229d0642e7fb474d2159700035ee7b0a150', '2026-10-05 21:15:26'),
(2, 1, 'abc3a47b8ad18b855c687d9ca2c6091ee7312db5563021942a57ada889c87b34', '2026-10-09 20:53:36'),
(4, 1, 'abc3a47b8ad18b855c687d9ca2c6091ee7312db5563021942a57ada889c87b34', '2026-10-09 21:15:53'),
(5, 1, 'd0b65965d8e78ef60abcd1a277383229d0642e7fb474d2159700035ee7b0a150', '2026-10-05 21:15:29'),
(8, 1, 'd0b65965d8e78ef60abcd1a277383229d0642e7fb474d2159700035ee7b0a150', '2026-10-05 21:17:55'),
(10, 1, 'd0b65965d8e78ef60abcd1a277383229d0642e7fb474d2159700035ee7b0a150', '2026-10-05 21:15:40'),
(12, 2, '0195616cec890a4dc990b9d0d41435fae026900a411f1fe03ba4c2e709701d69', '2026-10-06 00:43:29'),
(12, 1, '4f3e3b1de3b0f6d2c0350ca9d692b4cfb75e7951e5f8b2dbf1d36ea97891b7e7', '2026-10-09 21:17:10'),
(13, 1, 'abc3a47b8ad18b855c687d9ca2c6091ee7312db5563021942a57ada889c87b34', '2026-10-09 21:19:34'),
(21, 2, '0195616cec890a4dc990b9d0d41435fae026900a411f1fe03ba4c2e709701d69', '2026-10-06 00:43:39'),
(22, 1, 'd0b65965d8e78ef60abcd1a277383229d0642e7fb474d2159700035ee7b0a150', '2026-10-07 14:51:03'),
(29, 1, '5b090c911169da36688a137408fdbd4e12b5c7e68b9f1ab27072959f6973f746', '2026-09-30 15:41:21'),
(30, 1, 'd0b65965d8e78ef60abcd1a277383229d0642e7fb474d2159700035ee7b0a150', '2026-10-11 01:38:59'),
(31, 2, '451d62ea56667abae807a8cb5ea1984ef2938873406819c3455fc4094041e5fc', '2026-10-06 00:43:25'),
(31, NULL, '64212a87f059c2c094875f11a4f46a6e081c3d1b20cd036189392b084cb5c68c', '2026-09-29 19:50:28'),
(31, 1, '962bf0f186632cc8e79b6a369b85bb1a1a3a9c1776091553ad3bb13f5cb4d978', '2026-10-10 20:22:30'),
(32, 2, '0195616cec890a4dc990b9d0d41435fae026900a411f1fe03ba4c2e709701d69', '2026-10-09 20:55:39'),
(32, NULL, '102e4d5b88e49ba64a8f1b1507dff5ec367090fb6770be8d8dc257c67703b39d', '2026-10-09 20:55:33'),
(32, 1, '27a236ec50c319d8e8ed104b44304b2f614ebef0db1dc822fd21d06c28e520f2', '2026-10-11 01:38:57'),
(32, NULL, 'be7b44ddfe6a42cb743a6e3cbf31031eda9e810e9671564e16c2635cbd6bc101', '2026-10-02 21:00:13'),
(32, NULL, 'f0c5a0d6245302e6ba9e4115ffbd329fb59fcf96106cba2a26f2609ec8156354', '2026-10-09 20:55:41'),
(32, NULL, 'f9ccd8efdac62473c7c6c6d083286dd52c411c19eaa5e8d45cf4de64f8ba7c02', '2026-10-05 23:27:58'),
(33, 2, '451d62ea56667abae807a8cb5ea1984ef2938873406819c3455fc4094041e5fc', '2026-09-29 19:50:47'),
(33, 1, '5b090c911169da36688a137408fdbd4e12b5c7e68b9f1ab27072959f6973f746', '2026-10-09 21:04:52'),
(34, 1, '4f3e3b1de3b0f6d2c0350ca9d692b4cfb75e7951e5f8b2dbf1d36ea97891b7e7', '2026-09-30 15:37:16');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(6, 'Guides'),
(1, 'Nintendo'),
(4, 'PC'),
(2, 'PlayStation'),
(7, 'Review'),
(3, 'Xbox');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `parent_comment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `article_id`, `user_id`, `content`, `status`, `created_at`, `updated_at`, `parent_comment_id`) VALUES
(1, 31, 1, 'Okay, this actually has my attention. The world looks like it could be really fun to explore. wow', 1, '2026-09-18 10:15:00', '2026-09-18 19:22:14', NULL),
(2, 31, 3, 'I just hope there is more to do than simply following the main story. Give me lots of secrets to find!', 4, '2026-09-18 10:32:00', NULL, NULL),
(3, 31, 4, 'The art direction is really nice. It has that classic colorful Nintendo feel without looking exactly like their older games.', 1, '2026-09-18 10:47:00', NULL, NULL),
(4, 31, 5, 'Anyone else immediately wondering what the soundtrack is going to sound like? 👀', 1, '2026-09-18 11:03:00', NULL, NULL),
(5, 31, 6, 'Not gonna lie, I need a proper gameplay trailer before I get too excited. The reveal was way too short.', 1, '2026-09-18 11:21:00', NULL, NULL),
(6, 31, 7, 'The idea of having different environments to explore sounds great. Hopefully the map is actually worth getting lost in.', 1, '2026-09-18 11:46:00', NULL, NULL),
(7, 31, 8, 'This is definitely going on my watchlist. There are still so many unanswered questions though.', 1, '2026-09-18 12:08:00', NULL, NULL),
(8, 31, 9, 'I wonder if the characters will actually have their own abilities or if they are mainly there for the story.', 1, '2026-09-18 12:24:00', NULL, NULL),
(9, 31, 10, 'Same. I really want optional areas that actually reward you for exploring.', 1, '2026-09-18 10:51:00', NULL, 2),
(10, 31, 11, 'Exactly. Finding something completely unexpected is one of the best parts of these games.', 1, '2026-09-18 11:12:00', NULL, 9),
(11, 31, 12, 'I was thinking the same thing. A good soundtrack can make exploration feel so much better.', 1, '2026-09-18 11:18:00', NULL, 4),
(12, 31, 13, 'Yeah, I would rather wait for a longer gameplay reveal before judging it.', 1, '2026-09-18 11:39:00', NULL, 5),
(13, 31, 14, 'Hopefully! I want the world to have little details that make it worth exploring instead of just being a big empty map.', 1, '2026-09-18 12:01:00', NULL, 6),
(14, 31, 15, 'The character question is interesting. Giving them different abilities could make exploration a lot more interesting.', 1, '2026-09-18 12:36:00', NULL, 8),
(15, 31, 16, 'Honestly I am more interested in the gameplay than the story right now 😂', 1, '2026-09-18 12:52:00', NULL, 8),
(16, 31, 17, 'Same 😂. Hopefully the next trailer actually shows us what we will be doing moment to moment.', 1, '2026-09-18 13:07:00', NULL, 16),
(22, 31, 2, 'comment', 0, '2026-09-18 21:41:25', NULL, NULL),
(23, 31, 2, 'reply', 0, '2026-09-18 21:41:30', NULL, 22),
(24, 32, 2, 'amazing', 1, '2026-10-05 23:27:01', NULL, NULL),
(25, 32, 2, 'i love it', 1, '2026-10-05 23:27:18', NULL, 24);

-- --------------------------------------------------------

--
-- Table structure for table `comment_reaction`
--

CREATE TABLE `comment_reaction` (
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cosmetic`
--

CREATE TABLE `cosmetic` (
  `cosmetic_id` int(11) NOT NULL,
  `cosmetic_type_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `rarity` tinyint(4) NOT NULL,
  `price` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cosmetic`
--

INSERT INTO `cosmetic` (`cosmetic_id`, `cosmetic_type_id`, `name`, `image_url`, `rarity`, `price`) VALUES
(1, 1, 'Name1', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790598715/01.png', 1, 0),
(2, 1, 'Name2', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790673957/02.png', 1, 0),
(3, 1, 'Name3', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790769821/03.png', 1, 0),
(4, 1, 'Name4', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790686050/04.png', 1, 0),
(5, 1, 'Name5', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790686077/05.png', 1, 500),
(6, 1, 'Name6', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790686093/06.png', 1, 100),
(7, 1, 'Name7', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790686107/07.png', 1, 0),
(8, 1, 'Name8', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790686077/08.png', 1, 0),
(9, 2, 'day', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790698109/bg01.png', 1, 0),
(10, 2, 'night', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790698112/bg02.png', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cosmetic_type`
--

CREATE TABLE `cosmetic_type` (
  `cosmetic_type_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cosmetic_type`
--

INSERT INTO `cosmetic_type` (`cosmetic_type_id`, `code`) VALUES
(2, 'background'),
(1, 'profile frame');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification`
--

CREATE TABLE `email_verification` (
  `verification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `expire_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verification`
--

INSERT INTO `email_verification` (`verification_id`, `user_id`, `email`, `type`, `otp_hash`, `attempts`, `expire_at`, `created_at`) VALUES
(2, 1, 'junxmimori@gmail.com', 2, '$2y$10$Oz2L8s0j.eXKvkiA8W.zU.ALylaeGRyjjN7ifidnP4d25/HdMfFL2', 0, '2026-10-04 00:13:33', '2026-10-04 00:08:33'),
(3, NULL, 'junxmimori@gmail.com', 1, '$2y$10$4k1xKkS32S.bxrIHGMgvkOuuifiDo61ZFsoDQYUAMi9lR1VbOBxrS', 0, '2026-10-04 14:12:22', '2026-10-04 14:07:22'),
(4, NULL, 'dragrvejunz@gmail.com', 1, '$2y$10$4UGJndHG4NeeVbiqejtv6ufcxffbx8eGSaVypthEC7L9KG/bFmg5a', 0, '2026-10-04 14:21:13', '2026-10-04 14:16:13'),
(6, NULL, 'john123@test.com', 1, '$2y$10$BkQ5eZJkVZRgGREHvqoMN.8dVhdwUZGPMGO6OQxSJDwoGuNPW97My', 0, '2026-10-04 15:25:13', '2026-10-04 15:20:13');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `actor_id`, `article_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 1, 31, 1, 'edited an article: Nintendo Reveals a New Adventure Coming to Switch', 0, '2026-09-19 12:43:39'),
(2, 2, 1, 32, 1, 'published a new article: Trails in the Sky 2nd Chapter demo now available', 0, '2026-09-19 12:48:59'),
(11, 2, 1, 33, 1, 'published a new article: schedule', 0, '2026-09-28 17:48:00'),
(12, 2, 1, 32, 2, 'edited an article: Trails in the Sky 2nd Chapter demo now available', 0, '2026-09-27 14:56:27'),
(13, 2, 1, 31, 2, 'edited an article: Nintendo Reveals a New Adventure Coming to Switch', 0, '2026-09-30 14:35:02'),
(14, 2, 1, 32, 2, 'edited an article: Trails in the Sky 2nd Chapter demo now available', 0, '2026-09-30 14:36:15'),
(15, 2, 1, 32, 2, 'edited an article: Trails in the Sky 2nd Chapter demo now available', 0, '2026-09-30 14:36:22'),
(16, 2, 1, 32, 2, 'edited an article: Trails in the Sky 2nd Chapter demo now available', 0, '2026-09-30 14:36:26'),
(17, 2, 1, 32, 2, 'edited an article: Trails in the Sky 2nd Chapter demo now available', 1, '2026-09-30 14:36:28'),
(18, 2, 1, 34, 1, 'published a new article: draft', 0, '2026-09-30 15:37:15'),
(19, 2, 1, 33, 2, 'edited an article: schedule', 1, '2026-09-30 15:37:32'),
(24, 13, 1, NULL, 3, 'issued you a warning and removed your article. Reason: Your article violated our community guidelines.\n\nResolution ID: #10.', 0, '2026-10-09 19:24:46'),
(25, 12, 1, NULL, 4, 'suspended your account for 14 days and removed your article. Reason: Your article violated our community guidelines.\n\nResolution ID: #11.', 0, '2026-10-09 19:25:02'),
(26, 3, 1, NULL, 3, 'issued you a warning and removed your comment. Reason: Your comment violated our community guidelines.\n\nResolution ID: #6.', 0, '2026-10-10 20:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `report_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `resolution_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`report_id`, `article_id`, `comment_id`, `reporter_id`, `reason`, `resolution_id`, `created_at`) VALUES
(1, 2, NULL, 5, 'Misleading information', NULL, '2026-10-09 19:24:23'),
(2, 2, NULL, 8, 'Misleading information', NULL, '2026-10-09 19:24:23'),
(3, 2, NULL, 9, 'Spam', NULL, '2026-10-09 19:24:23'),
(4, 3, NULL, 6, 'Misleading information', NULL, '2026-10-09 19:24:23'),
(5, 3, NULL, 8, 'Spam', NULL, '2026-10-09 19:24:23'),
(6, 4, NULL, 2, 'Misleading information', 5, '2026-10-09 19:24:23'),
(7, 5, NULL, 2, 'Spam', NULL, '2026-10-09 19:24:23'),
(8, 6, NULL, 2, 'Misleading information', NULL, '2026-10-09 19:24:23'),
(9, 7, NULL, 2, 'Spam', NULL, '2026-10-09 19:24:23'),
(10, 8, NULL, 2, 'Misleading information', NULL, '2026-10-09 19:24:23'),
(11, 9, NULL, 2, 'Spam', NULL, '2026-10-09 19:24:23'),
(12, 10, NULL, 2, 'Misleading information', 2, '2026-10-09 19:24:23'),
(13, 11, NULL, 2, 'Spam', 1, '2026-10-09 19:24:23'),
(14, 12, NULL, 2, 'Misleading information', 4, '2026-10-09 19:24:23'),
(15, 13, NULL, 2, 'Spam', 3, '2026-10-09 19:24:23'),
(16, NULL, 2, 6, 'Harassment', 6, '2026-10-09 19:24:23'),
(17, NULL, 2, 8, 'Harassment', 6, '2026-10-09 19:24:23'),
(18, NULL, 3, 7, 'Spam', NULL, '2026-10-09 19:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `report_resolution`
--

CREATE TABLE `report_resolution` (
  `resolution_id` int(11) NOT NULL,
  `action` enum('dismissed','warning','suspension','ban') NOT NULL,
  `admin_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `resolved_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_resolution`
--

INSERT INTO `report_resolution` (`resolution_id`, `action`, `admin_id`, `notes`, `resolved_at`) VALUES
(1, 'dismissed', 1, NULL, '2026-10-09 19:24:33'),
(2, 'dismissed', 1, NULL, '2026-10-09 19:24:37'),
(3, 'warning', 1, NULL, '2026-10-09 19:24:46'),
(4, 'suspension', 1, NULL, '2026-10-09 19:25:02'),
(5, 'ban', 1, NULL, '2026-10-09 19:25:11'),
(6, 'warning', 1, NULL, '2026-10-10 20:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'user'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `tag_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`tag_id`, `name`) VALUES
(1, 'Action'),
(2, 'Adventure'),
(21, 'Battle Royale'),
(29, 'Card Game'),
(12, 'Fighting'),
(10, 'FPS'),
(15, 'Horror'),
(4, 'JRPG'),
(24, 'Metroidvania'),
(19, 'MMORPG'),
(20, 'MOBA'),
(27, 'Open World'),
(28, 'Party'),
(8, 'Platformer'),
(7, 'Puzzle'),
(13, 'Racing'),
(18, 'Rhythm'),
(22, 'Roguelike'),
(23, 'Roguelite'),
(3, 'RPG'),
(26, 'Sandbox'),
(9, 'Shooter'),
(6, 'Simulation'),
(14, 'Sports'),
(17, 'Stealth'),
(5, 'Strategy'),
(16, 'Survival'),
(11, 'TPS'),
(30, 'Turn-Based'),
(25, 'Visual Novel');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `reason` varchar(30) NOT NULL,
  `cosmetic_id` int(11) DEFAULT NULL,
  `reference_key` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `user_id`, `amount`, `reason`, `cosmetic_id`, `reference_key`, `created_at`) VALUES
(25, 1, 0, 'shop_purchase', 1, 'shop_purchase:1', '2026-10-05 00:34:49'),
(26, 1, 0, 'shop_purchase', 2, 'shop_purchase:2', '2026-10-05 00:35:01'),
(27, 1, 0, 'shop_purchase', 3, 'shop_purchase:3', '2026-10-05 00:35:42'),
(28, 1, 100, 'daily_login', NULL, 'daily_login:2026-10-05', '2026-10-05 17:31:12'),
(29, 1, 50, 'daily_mission', NULL, 'mission:read_articles:2026-10-05', '2026-10-05 21:15:44'),
(36, 2, 100, 'daily_login', NULL, 'daily_login:2026-10-06', '2026-10-06 00:42:33'),
(37, 2, 20, 'daily_mission', NULL, 'mission:read_articles:2026-10-06', '2026-10-06 00:43:45'),
(38, 1, 100, 'daily_login', NULL, 'daily_login:2026-10-07', '2026-10-07 22:49:26'),
(39, 1, 100, 'daily_login', NULL, 'daily_login:2026-10-08', '2026-10-08 22:54:36'),
(40, 1, -500, 'shop_purchase', 5, 'shop_purchase:5', '2026-10-08 23:18:03'),
(41, 1, 100, 'daily_login', NULL, 'daily_login:2026-10-09', '2026-10-09 22:52:52'),
(42, 1, 100, 'daily_login', NULL, 'daily_login:2026-10-11', '2026-10-11 01:31:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` varchar(160) NOT NULL DEFAULT 'This user prefers to keep things a mystery.',
  `role_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `suspended_until` datetime DEFAULT NULL,
  `coin` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `profile_picture`, `bio`, `role_id`, `status`, `suspended_until`, `coin`) VALUES
(1, 'john', 'john@test.com', '$2y$10$QBi18IpHKKg7IwVggtRScudlvyFbxPxeP3ZkX16xN7MJ/dECG8kge', 'https://res.cloudinary.com/bl7j2jno/image/upload/v1790816370/FYP-NRS/profilepic/bfmizypljm8td9nw9wlb.png', 'new bio', 2, 1, NULL, 450),
(2, 'John2', 'john2@test.com', '$2y$10$QBi18IpHKKg7IwVggtRScudlvyFbxPxeP3ZkX16xN7MJ/dECG8kge', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 120),
(3, 'Alice', 'alice@test.com', '$2y$10$9zc8LRpNOevFc365SGvE4ueeuqk8pbWP.ySHox8Kfql4QyqsHwSjC', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(4, 'Bob', 'bob@test.com', '$2y$10$AAff8V3tWThxSPHTZjeTn.TSrycexVlIv/afGRKlEfnmfkP3Gn81O', NULL, 'This user prefers to keep things a mystery.', 1, 4, NULL, 0),
(5, 'Charlie', 'charlie@test.com', '$2y$10$odZ3w1VEzS7SZ9GyzeFyae.Qv1P4.Hy3fOIBU6l85VJci9vNraVKe', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(6, 'David', 'david@test.com', '$2y$10$LThkJ.b7YZIn7OT9slpwP.5KiknDJeubNqK/k3hPup46DN1qRXVfy', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(7, 'Emma', 'emma@test.com', '$2y$10$TP2TsoBsgsqphw8ftxGUEuNIUBFZlpYGJg38iglRwi2kCdTnfbwne', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(8, 'Fiona', 'fiona@test.com', '$2y$10$BVI0pBz2XFAcT4y0iquqSeejORBTflzTgGYn4aanUpdiT3FmcF8HK', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(9, 'George', 'george@test.com', '$2y$10$/2j2gW66lNdmQkGJiCvkZ.QE2oT2803dgHyA76Vg7qBVmxhkHBD56', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(10, 'Hannah', 'hannah@test.com', '$2y$10$C.GY6tGsYy/VhkoqEtzAlOF28AjC3C.t8m5/xG1wX5SljwenJRG.e', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(11, 'Isaac', 'isaac@test.com', '$2y$10$DgrivSZVIZVFNpwxMdWZxu3VeU6IMPgqG9vN74fA7SYFS4nIaG65.', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(12, 'Julia', 'julia@test.com', '$2y$10$kE9ryyEE7rMmndeXTWi5V.9EjMXFA7u20qfLxQoVs7MYft5FodJOK', NULL, 'This user prefers to keep things a mystery.', 1, 1, '2026-10-23 19:25:02', 0),
(13, 'Kevin', 'kevin@test.com', '$2y$10$V8Qnhzk.X.84q0CTm8jVC.h40FjHfp7Mxbd3KSGoLzoFF4JCM6BwO', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(14, 'Lily', 'lily@test.com', '$2y$10$m/9s9.gvNXOLsdyhELVx5OAihL4EjV7iLcEsdHl9SwGMnVDVG2ZDG', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(15, 'Mason', 'mason@test.com', '$2y$10$grJU5xfzY6s9UYP6pwzxo.dnAgCvMKzBl4HCY85WvSg9XIY81UT0.', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(16, 'Nora', 'nora@test.com', '$2y$10$B/ZsvxzJrwUlny20wCGuD.jQ2RLRscDm7bHRD96olKMXX/WnBdNuG', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(17, 'Owen', 'owen@test.com', '$2y$10$V5Jj73iREv0DVzukWhZmXu5XrxSjoegVXHRfqjPxguSCReJKur8Si', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(18, 'Priya', 'priya@test.com', '$2y$10$ZnDYT.Nri4i2CYLBy6yN1.mGzRRn2liJXvUaJ/ynSOxJVbYBU0n.K', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(19, 'Quinn', 'quinn@test.com', '$2y$10$AE93kIzLNlnp/KdMvADGteUOstm3wadPiu0LDLlnc0Lvtz0BY9lcK', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0),
(20, 'Ryan', 'ryan@test.com', '$2y$10$tCrfgTui.Pl30R4QAGJLzuw97WA08OEHq43YgMD4ym4Aj1M.sHzTm', NULL, 'This user prefers to keep things a mystery.', 1, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_cosmetic`
--

CREATE TABLE `user_cosmetic` (
  `user_id` int(11) NOT NULL,
  `cosmetic_id` int(11) NOT NULL,
  `obtained_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_cosmetic`
--

INSERT INTO `user_cosmetic` (`user_id`, `cosmetic_id`, `obtained_at`) VALUES
(1, 1, '2026-10-05 00:34:49'),
(1, 2, '2026-10-05 00:35:01'),
(1, 3, '2026-10-05 00:35:42'),
(1, 4, '2026-09-29 20:50:44'),
(1, 5, '2026-10-08 23:18:03'),
(1, 6, '2026-10-04 23:56:14'),
(1, 7, '2026-09-29 20:50:57'),
(1, 8, '2026-09-29 20:51:01'),
(1, 9, '2026-10-04 23:54:43'),
(1, 10, '2026-10-04 23:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_equipped_cosmetic`
--

CREATE TABLE `user_equipped_cosmetic` (
  `user_id` int(11) NOT NULL,
  `cosmetic_type_id` int(11) NOT NULL,
  `cosmetic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_equipped_cosmetic`
--

INSERT INTO `user_equipped_cosmetic` (`user_id`, `cosmetic_type_id`, `cosmetic_id`) VALUES
(1, 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `user_moderation_history`
--

CREATE TABLE `user_moderation_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` enum('warning','suspension','ban','lift_suspension','unban') NOT NULL,
  `reason` text NOT NULL,
  `suspended_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_moderation_history`
--

INSERT INTO `user_moderation_history` (`history_id`, `user_id`, `admin_id`, `action`, `reason`, `suspended_until`, `created_at`) VALUES
(1, 13, 1, 'warning', 'Your article violated our community guidelines.', NULL, '2026-10-09 19:24:46'),
(2, 12, 1, 'suspension', 'Your article violated our community guidelines.', '2026-10-23 19:25:02', '2026-10-09 19:25:02'),
(3, 4, 1, 'ban', 'Your article violated our community guidelines.', NULL, '2026-10-09 19:25:11'),
(4, 3, 1, 'warning', 'Your comment violated our community guidelines.', NULL, '2026-10-10 20:22:46'),
(5, 4, 1, 'warning', 'Test warning 1: spam in comments.', NULL, '2026-10-10 22:07:09'),
(6, 4, 1, 'warning', 'Test warning 2: misleading information.', NULL, '2026-10-10 22:07:09'),
(7, 4, 1, 'warning', 'Test warning 3: harassment in replies.', NULL, '2026-10-10 22:07:09'),
(8, 4, 1, 'warning', 'Test warning 4: repeated promotional links.', NULL, '2026-10-10 22:07:09'),
(9, 4, 1, 'warning', 'Test warning 5: inappropriate language.', NULL, '2026-10-10 22:07:09'),
(10, 4, 1, 'warning', 'Test warning 6: personal attacks.', NULL, '2026-10-10 22:07:09'),
(11, 4, 1, 'warning', 'Test warning 7: off-topic spam.', NULL, '2026-10-10 22:07:09'),
(12, 4, 1, 'warning', 'Test warning 8: repeated misleading claims.', NULL, '2026-10-10 22:07:09'),
(13, 4, 1, 'warning', 'Test warning 9: disruptive behaviour.', NULL, '2026-10-10 22:07:09'),
(14, 4, 1, 'warning', 'Test warning 10: another guideline violation.', NULL, '2026-10-10 22:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscription`
--

CREATE TABLE `user_subscription` (
  `subscriber_id` int(11) NOT NULL,
  `subscribed_to_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_subscription`
--

INSERT INTO `user_subscription` (`subscriber_id`, `subscribed_to_id`) VALUES
(1, 8),
(1, 10),
(2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_token`
--
ALTER TABLE `account_token`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_article_score_calculated_at` (`score_calculated_at`),
  ADD KEY `author_id` (`author_id`) USING BTREE,
  ADD KEY `background_cosmetic_id` (`background_cosmetic_id`);

--
-- Indexes for table `article_ai_summary`
--
ALTER TABLE `article_ai_summary`
  ADD PRIMARY KEY (`article_id`);

--
-- Indexes for table `article_bookmark`
--
ALTER TABLE `article_bookmark`
  ADD PRIMARY KEY (`article_id`,`user_id`),
  ADD KEY `article_bookmark_ibfk_2` (`user_id`);

--
-- Indexes for table `article_reaction`
--
ALTER TABLE `article_reaction`
  ADD PRIMARY KEY (`article_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `article_tag`
--
ALTER TABLE `article_tag`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `article_tag_ibfk_2` (`tag_id`);

--
-- Indexes for table `article_view`
--
ALTER TABLE `article_view`
  ADD PRIMARY KEY (`article_id`,`viewer_key`),
  ADD UNIQUE KEY `article_id` (`article_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`category_name`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comment_ibfk_3` (`parent_comment_id`);

--
-- Indexes for table `comment_reaction`
--
ALTER TABLE `comment_reaction`
  ADD PRIMARY KEY (`comment_id`,`user_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cosmetic`
--
ALTER TABLE `cosmetic`
  ADD PRIMARY KEY (`cosmetic_id`),
  ADD UNIQUE KEY `cosmetic_id` (`cosmetic_id`,`cosmetic_type_id`),
  ADD KEY `cosmetic_ibfk_1` (`cosmetic_type_id`);

--
-- Indexes for table `cosmetic_type`
--
ALTER TABLE `cosmetic_type`
  ADD PRIMARY KEY (`cosmetic_type_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `email_verification`
--
ALTER TABLE `email_verification`
  ADD PRIMARY KEY (`verification_id`),
  ADD UNIQUE KEY `email` (`email`,`type`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `notification_ibfk_1` (`article_id`),
  ADD KEY `notification_ibfk_3` (`actor_id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `article_id` (`article_id`,`reporter_id`),
  ADD UNIQUE KEY `comment_id` (`comment_id`,`reporter_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `resolution_id` (`resolution_id`);

--
-- Indexes for table `report_resolution`
--
ALTER TABLE `report_resolution`
  ADD PRIMARY KEY (`resolution_id`),
  ADD KEY `report_resolution_ibfk_1` (`admin_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`reference_key`),
  ADD KEY `transaction_ibfk_2` (`cosmetic_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_ibfk_1` (`role_id`);

--
-- Indexes for table `user_cosmetic`
--
ALTER TABLE `user_cosmetic`
  ADD PRIMARY KEY (`user_id`,`cosmetic_id`),
  ADD KEY `user_cosmetic_ibfk_2` (`cosmetic_id`);

--
-- Indexes for table `user_equipped_cosmetic`
--
ALTER TABLE `user_equipped_cosmetic`
  ADD PRIMARY KEY (`user_id`,`cosmetic_type_id`),
  ADD KEY `user_id` (`user_id`,`cosmetic_id`),
  ADD KEY `user_equipped_cosmetic_ibfk_2` (`cosmetic_id`,`cosmetic_type_id`);

--
-- Indexes for table `user_moderation_history`
--
ALTER TABLE `user_moderation_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `user_subscription`
--
ALTER TABLE `user_subscription`
  ADD PRIMARY KEY (`subscriber_id`,`subscribed_to_id`),
  ADD KEY `subscribed_to_id` (`subscribed_to_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_token`
--
ALTER TABLE `account_token`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `article`
--
ALTER TABLE `article`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cosmetic`
--
ALTER TABLE `cosmetic`
  MODIFY `cosmetic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cosmetic_type`
--
ALTER TABLE `cosmetic_type`
  MODIFY `cosmetic_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_verification`
--
ALTER TABLE `email_verification`
  MODIFY `verification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_resolution`
--
ALTER TABLE `report_resolution`
  MODIFY `resolution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_moderation_history`
--
ALTER TABLE `user_moderation_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_token`
--
ALTER TABLE `account_token`
  ADD CONSTRAINT `account_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `article_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `article_ibfk_3` FOREIGN KEY (`background_cosmetic_id`) REFERENCES `cosmetic` (`cosmetic_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `article_ai_summary`
--
ALTER TABLE `article_ai_summary`
  ADD CONSTRAINT `article_ai_summary_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `article_bookmark`
--
ALTER TABLE `article_bookmark`
  ADD CONSTRAINT `article_bookmark_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `article_bookmark_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `article_reaction`
--
ALTER TABLE `article_reaction`
  ADD CONSTRAINT `article_reaction_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `article_reaction_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `article_tag`
--
ALTER TABLE `article_tag`
  ADD CONSTRAINT `article_tag_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `article_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `article_view`
--
ALTER TABLE `article_view`
  ADD CONSTRAINT `article_view_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `article_view_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comment` (`comment_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment_reaction`
--
ALTER TABLE `comment_reaction`
  ADD CONSTRAINT `comment_reaction_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comment` (`comment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_reaction_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `cosmetic`
--
ALTER TABLE `cosmetic`
  ADD CONSTRAINT `cosmetic_ibfk_1` FOREIGN KEY (`cosmetic_type_id`) REFERENCES `cosmetic_type` (`cosmetic_type_id`) ON UPDATE CASCADE;

--
-- Constraints for table `email_verification`
--
ALTER TABLE `email_verification`
  ADD CONSTRAINT `email_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `notification_ibfk_3` FOREIGN KEY (`actor_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comment` (`comment_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `report_ibfk_3` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `report_ibfk_4` FOREIGN KEY (`resolution_id`) REFERENCES `report_resolution` (`resolution_id`) ON UPDATE CASCADE;

--
-- Constraints for table `report_resolution`
--
ALTER TABLE `report_resolution`
  ADD CONSTRAINT `report_resolution_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`cosmetic_id`) REFERENCES `cosmetic` (`cosmetic_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_cosmetic`
--
ALTER TABLE `user_cosmetic`
  ADD CONSTRAINT `user_cosmetic_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_cosmetic_ibfk_2` FOREIGN KEY (`cosmetic_id`) REFERENCES `cosmetic` (`cosmetic_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_equipped_cosmetic`
--
ALTER TABLE `user_equipped_cosmetic`
  ADD CONSTRAINT `user_equipped_cosmetic_ibfk_1` FOREIGN KEY (`user_id`,`cosmetic_id`) REFERENCES `user_cosmetic` (`user_id`, `cosmetic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_equipped_cosmetic_ibfk_2` FOREIGN KEY (`cosmetic_id`,`cosmetic_type_id`) REFERENCES `cosmetic` (`cosmetic_id`, `cosmetic_type_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_moderation_history`
--
ALTER TABLE `user_moderation_history`
  ADD CONSTRAINT `user_moderation_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_moderation_history_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_subscription`
--
ALTER TABLE `user_subscription`
  ADD CONSTRAINT `user_subscription_ibfk_1` FOREIGN KEY (`subscribed_to_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_subscription_ibfk_2` FOREIGN KEY (`subscriber_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
