<div>
<h4>Scrape Boxscore</h4>
<form name="input" action="scrape/scrapeBoxScore" method="post">
<table>
	<tr>
		<td><label for='season'>Season:</label></td>
		<td>
			<select name="season" id='season'>
				<option value="20102011" selected>20102011</option>
				<option value="20092010">20092010</option>
				<option value="20082009">20082009</option>
				<option value="20072008">20072008</option>
			</select>
		</td>
	</tr>
		<td><label for='gametype'>Gametype:</label></td>
		<td>
			<select name="gametype" id='gametype'>
				<option value="2" selected>Regular Season</option>
				<option value="3">Playoff</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><label for='game_id'>GameId:</label></td><td><input type="text" name="game_id" /></td>
	</tr>
	<tr>
		<td><label for='date'>Game Date (ex 2009-03-21):</label></td><td><input type="text" name="date" /></td>
	</tr>
	<tr>
		<td><label for='debug'>Debug:</label></td>
				<td>
			<select name="debug" id='debug'>
				<option value="1" selected>1</option>
				<option value="0">0</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><input type="submit"></td>
	</tr>
</table>
</div>