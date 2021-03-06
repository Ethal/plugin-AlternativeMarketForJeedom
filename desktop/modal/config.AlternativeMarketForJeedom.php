<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div id="div_pluginAlternativeMarketForJeedomAlert"></div>
<div id="config-modal">
    <div class="container">
        <h3>Liste des utilisateur GitHub</h3>
        <ul id="gituser-list" class="list-group">
        </ul>
        <div class="input-group">
            <input id="git-user" type="text" class="form-control" placeholder="Nom d'utilisateur GitHub..."/>
            <span class="input-group-btn">
            <button id="add-git" class="btn btn-primary" type="button"><i class="fa fa-plus"></i></button>
        </span>
        </div>
    </div>
</div>
<script src="plugins/AlternativeMarketForJeedom/desktop/js/config.AlternativeMarketForJeedom.js"></script>