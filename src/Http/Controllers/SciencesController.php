<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\AsasContainer;

/**
 * Sciences Controller
 *
 * Handles Islamic sciences content including:
 * - Fiqh (Islamic jurisprudence)
 * - Aqeedah (Islamic theology)
 * - Usul al-Fiqh (Principles of jurisprudence)
 * - Hadith sciences
 * - Quranic sciences
 * - Islamic history
 * - Arabic language and grammar
 */
class SciencesController extends Controller
{
    protected $logger;

    public function __construct(Connection $db, AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->logger = $container->get('logger');
    }

    /**
     * Display the Islamic Sciences main page
     */
    public function index(Request $request): Response
    {
        $this->logger->info('Islamic Sciences page requested', [
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
        ]);

        return $this->view('sciences/index', [
            'title' => 'Islamic Sciences',
            'description' => 'Explore the comprehensive field of Islamic sciences including Fiqh, Aqeedah, Usul al-Fiqh, and more.',
            'sciences' => [
                'fiqh' => [
                    'title' => 'Fiqh (Islamic Jurisprudence)',
                    'description' => 'The study of Islamic law and legal methodology',
                    'icon' => '⚖️',
                    'url' => '/sciences/fiqh'
                ],
                'aqeedah' => [
                    'title' => 'Aqeedah (Islamic Theology)',
                    'description' => 'The study of Islamic beliefs and creed',
                    'icon' => '🕌',
                    'url' => '/sciences/aqeedah'
                ],
                'usul' => [
                    'title' => 'Usul al-Fiqh (Principles of Jurisprudence)',
                    'description' => 'The methodology and principles of Islamic law',
                    'icon' => '📚',
                    'url' => '/sciences/usul'
                ],
                'hadith_sciences' => [
                    'title' => 'Hadith Sciences',
                    'description' => 'The study of hadith methodology and authentication',
                    'icon' => '📖',
                    'url' => '/sciences/hadith-sciences'
                ],
                'quranic_sciences' => [
                    'title' => 'Quranic Sciences',
                    'description' => 'The study of Quranic interpretation and sciences',
                    'icon' => '📜',
                    'url' => '/sciences/quranic-sciences'
                ],
                'arabic' => [
                    'title' => 'Arabic Language & Grammar',
                    'description' => 'The study of Arabic language, grammar, and rhetoric',
                    'icon' => '🔤',
                    'url' => '/sciences/arabic'
                ],
                'history' => [
                    'title' => 'Islamic History',
                    'description' => 'The study of Islamic civilization and history',
                    'icon' => '🏛️',
                    'url' => '/sciences/history'
                ]
            ]
        ]);
    }

    /**
     * Display a specific science category
     */
    public function category(Request $request, string $category): Response
    {
        $this->logger->info('Islamic Science category requested', [
            'category' => $category,
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
        ]);

        $categories = [
            'fiqh' => [
                'title' => 'Fiqh (Islamic Jurisprudence)',
                'description' => 'The study of Islamic law and legal methodology',
                'content' => 'Fiqh is the human understanding and practice of Islamic law. It is based on the Quran, Sunnah, and scholarly consensus.',
                'topics' => ['Prayer', 'Fasting', 'Zakat', 'Hajj', 'Marriage', 'Business', 'Criminal Law']
            ],
            'aqeedah' => [
                'title' => 'Aqeedah (Islamic Theology)',
                'description' => 'The study of Islamic beliefs and creed',
                'content' => 'Aqeedah deals with the fundamental beliefs of Islam, including the oneness of Allah, prophethood, and the hereafter.',
                'topics' => ['Tawhid', 'Prophethood', 'Hereafter', 'Divine Names', 'Angels', 'Books']
            ],
            'usul' => [
                'title' => 'Usul al-Fiqh (Principles of Jurisprudence)',
                'description' => 'The methodology and principles of Islamic law',
                'content' => 'Usul al-Fiqh provides the methodology for deriving Islamic law from its sources.',
                'topics' => ['Sources of Law', 'Analogy', 'Consensus', 'Public Interest', 'Custom']
            ]
        ];

        if (!isset($categories[$category])) {
            return $this->redirect('/sciences', 404);
        }

        return $this->view('sciences/category', [
            'category' => $categories[$category],
            'categoryKey' => $category
        ]);
    }
}
