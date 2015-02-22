var DrawStats=  new function(){
	this.divTodaysScores = null;
	this.divSecondaryScores = null;
	this.divActiveGameInfo = null;
	this.topLeaderPoints = null;
	this.topLeaderGoals = null;
	this.topLeaderAssists = null;
	this.topLeaderPIM = null;
	this.topLeaderP_M = null;
	this.topLeaderWins = null;
	this.topLeaderSVP = null;
	this.topLeaderGAA = null;
	this.topLeaderSO = null;
	this.standingsContainer = null;
	this.proc = 'done';
	this.procInterval = null;
	this.init = function(){
		var self = DrawStats;
		self.divTodaysScores ='tdScoresCont';
		self.divSecondaryScores = 'tdYestScoresCont';
		self.divActiveGameInfo = 'activeGameInfo';

		self.topLeaderPoints = 'topLeaderPoints';
		self.topLeaderGoals = 'topLeaderGoals';
		self.topLeaderAssists = 'topLeaderAssists';
		self.topLeaderPIM = 'topLeaderPIM';
		self.topLeaderP_M = 'topLeaderP_M';

		self.topLeaderWins = 'topLeaderWins';
		self.topLeaderSVP = 'topLeaderSVP';
		self.topLeaderGAA = 'topLeaderGAA';
		self.topLeaderSO = 'topLeaderSO';

		self.standingsContainer = document.getElementById('teamStandingsData');

		self.divLeaderContainer = null;
		self.divActiveGame = null;
		self.contPoints = null;
		self.scTop = null;
		self.scPeriods = null;
		self.scScroll = null;
		self.ajaxGameUpd = '/home/gameInfo';
		self.imagesURL = "/images/logo/";
		self.startProc();
	}
	this.cleanup = function(){
		DrawStats.contPoints = null;
		DrawStats.divLeaderContainer = null;
		DrawStats.divTodaysScores = null;
		DrawStats.divSecondaryScores = null;
		DrawStats.divActiveGameInfo = null;
		DrawStats.divActiveGame = null;
		DrawStats.scTop = null;
		DrawStats.scPeriods = null;
		DrawStats.scScroll = null;
		DrawStats.topLeaderPoints = null;
		DrawStats.topLeaderGoals = null;
		DrawStats.topLeaderAssists = null;
		DrawStats.topLeaderPIM = null;
		DrawStats.topLeaderP_M = null;
		DrawStats.topLeaderWins = null;
		DrawStats.topLeaderSVP = null;
		DrawStats.topLeaderGAA = null;
		DrawStats.topLeaderSO = null;
		DrawStats.standingData = null;
		DrawStats.divActiveGame = null;
	}
	this.startProc = function(){
		//Needs to draw todays games, secondary games, active game, standings and leader.
		var self = DrawStats;
		self.drawGameList(self.divTodaysScores,StatsDaily.data.todayGameList);
		self.drawGameList(self.divSecondaryScores,StatsDaily.data.secondaryGameList);
		self.drawLeaderList('arrPlayoffLeaders');
		self.drawGoalieLeaderList('arrGlPlayoffLeaders');
		self.drawStandings('season','Eastern',1);
	}
	this.drawStandings = function(type,conf, div){
		var self = DrawStats;
	
		if (div==1)
		{		
			self.drawStandingsDivision(type,conf);
		}
		else self.drawStandingsConf(type,conf);
	}
	this.drawStandingsDivision = function(type,conf){
		var self = DrawStats;
		var currentDivision ='';
		var currentConf =conf;
		
		//Step One, clean container
		while (self.standingsContainer.firstChild) {
			self.standingsContainer.removeChild(self.standingsContainer.firstChild);
		}
		//step two, parse through array and build DOM
		standingData = StatsDaily.data.standings[type]['div'];

		for(var x = 0;x < standingData.length;x++){
			//if div == conf, list all in conf, else group by div
			if(standingData[x].conference_name == conf && currentDivision!=standingData[x].divison_name){
				//new division
				var	divDivisionContainer = document.createElement("div");
				divDivisionContainer.className = "division";
					var	divDivisionTitle = document.createElement("div");
					divDivisionTitle.className = "standTeamName col";
					txtDivisionTitle = document.createTextNode(standingData[x].divison_name);
					divDivisionTitle.appendChild(txtDivisionTitle);
					var	divDivisionTitle2 = document.createElement("div");
					divDivisionTitle2.className = "standGP col";
					txtDivisionTitle2 = document.createTextNode('GP');
					divDivisionTitle2.appendChild(txtDivisionTitle2);
					var	divDivisionTitle3 = document.createElement("div");
					divDivisionTitle3.className = "standPT col";
					txtDivisionTitle3 = document.createTextNode('PT');
					divDivisionTitle3.appendChild(txtDivisionTitle3);

				divDivisionContainer.appendChild(divDivisionTitle);
				divDivisionContainer.appendChild(divDivisionTitle2);
				divDivisionContainer.appendChild(divDivisionTitle3);
				self.standingsContainer.appendChild(divDivisionContainer);
				currentDivision = standingData[x].divison_name;
			}
			if(standingData[x].conference_name == conf){
				var	divDivisionContainer = document.createElement("div");
				divDivisionContainer.className = "standTeamRow";
					var	divDivisionTitle = document.createElement("div");
					divDivisionTitle.className = "standTeamName col";
					txtDivisionTitle = document.createTextNode(standingData[x].team_name);
					divDivisionTitle.appendChild(txtDivisionTitle);
					var	divDivisionTitle2 = document.createElement("div");
					divDivisionTitle2.className = "standGP col";
					txtDivisionTitle2 = document.createTextNode(standingData[x].GP);
					divDivisionTitle2.appendChild(txtDivisionTitle2);
					var	divDivisionTitle3 = document.createElement("div");
					divDivisionTitle3.className = "standPT col";
					txtDivisionTitle3 = document.createTextNode(standingData[x].points);
					divDivisionTitle3.appendChild(txtDivisionTitle3);

				divDivisionContainer.appendChild(divDivisionTitle);
				divDivisionContainer.appendChild(divDivisionTitle2);
				divDivisionContainer.appendChild(divDivisionTitle3);
				self.standingsContainer.appendChild(divDivisionContainer);
			}
			
		}
	}
	this.drawStandingsConf = function(type,conf){
		var self = DrawStats;
		var currentDivision ='';
		var currentHigh = new Array();
		var currentRow = new Array();
		currentHigh['Southeast'] =0;
		currentHigh['Atlantic'] =0;
		currentHigh['Northeast'] =0;
		currentHigh['Pacific'] =0;
		currentHigh['Northwest'] =0;
		currentHigh['Central'] =0;
		currentRow['Southeast'] =0;
		currentRow['Atlantic'] =0;
		currentRow['Northeast'] =0;
		currentRow['Pacific'] =0;
		currentRow['Northwest'] =0;
		currentRow['Central'] =0;
		var topDivs = new Array();
		var otherTeams = new Array();
		var currentConf =conf;
		var z =0;
			//	alert(conf)
		//Step One, clean container
		while (self.standingsContainer.firstChild) {
			self.standingsContainer.removeChild(self.standingsContainer.firstChild);
		}
		//step two, parse through array and build DOM
		standingData = StatsDaily.data.standings[type]['conf'];
		//alert(standingData);

		//Draw header
		var	divDivisionContainer = document.createElement("div");
		divDivisionContainer.className = "division";
			var	divDivisionTitle = document.createElement("div");
			divDivisionTitle.className = "standTeamName col";
			txtDivisionTitle = document.createTextNode(conf);
			divDivisionTitle.appendChild(txtDivisionTitle);
			var	divDivisionTitle2 = document.createElement("div");
			divDivisionTitle2.className = "standGP col";
			txtDivisionTitle2 = document.createTextNode('GP');
			divDivisionTitle2.appendChild(txtDivisionTitle2);
			var	divDivisionTitle3 = document.createElement("div");
			divDivisionTitle3.className = "standPT col";
			txtDivisionTitle3 = document.createTextNode('PT');
			divDivisionTitle3.appendChild(txtDivisionTitle3);

		divDivisionContainer.appendChild(divDivisionTitle);
		divDivisionContainer.appendChild(divDivisionTitle2);
		divDivisionContainer.appendChild(divDivisionTitle3);
		self.standingsContainer.appendChild(divDivisionContainer);

		//get top 3 

		for(var x = 0;x < standingData.length;x++){
		//if div == conf, list all in conf, else group by div
			if( standingData[x].conference_name == conf){
				if( standingData[x].divison_name == 'Southeast'){
					if(parseInt(currentHigh['Southeast']) < (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						currentHigh['Southeast'] = parseInt(standingData[x].points);
						currentRow['Southeast'] = parseInt(standingData[x].ROW);
						topDivs[0]=(standingData[x].team_name);
					}
					else if(parseInt(currentHigh['Southeast']) == (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						if(parseInt(currentRow['Southeast']) < parseInt(standingData[x].ROW)){
						currentHigh['Southeast'] = parseInt(standingData[x].points);
						currentRow['Southeast'] = parseInt(standingData[x].ROW);
						topDivs[0]=(standingData[x].team_name);
						}
					}
				}
				if( standingData[x].divison_name == 'Atlantic'){
					if(parseInt(currentHigh['Atlantic']) < (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						currentHigh['Atlantic'] = parseInt(standingData[x].points);
						currentRow['Atlantic'] = parseInt(standingData[x].ROW);
						topDivs[1]=(standingData[x].team_name);

					}
					else if(parseInt(currentHigh['Atlantic']) == (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						if(parseInt(currentRow['Atlantic']) < parseInt(standingData[x].ROW)){
						currentHigh['Atlantic'] = parseInt(standingData[x].points);
						currentRow['Atlantic'] = parseInt(standingData[x].ROW);
						topDivs[1]=(standingData[x].team_name);
						}
					}
				}
				if( standingData[x].divison_name == 'Northeast'){
					if(parseInt(currentHigh['Northeast']) <(parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						currentHigh['Northeast'] = parseInt(standingData[x].points);
						currentRow['Northeast'] = parseInt(standingData[x].ROW);
						topDivs[2]=(standingData[x].team_name);

					}
					else if(parseInt(currentHigh['Northeast']) == (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						if(parseInt(currentRow['Northeast']) < parseInt(standingData[x].ROW)){
						currentHigh['Northeast'] = parseInt(standingData[x].points);
						currentRow['Northeast'] = parseInt(standingData[x].ROW);
						topDivs[2]=(standingData[x].team_name);
						}
					}
				}
				if( standingData[x].divison_name == 'Pacific'){
					if(parseInt(currentHigh['Pacific']) < (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						currentHigh['Pacific'] = parseInt(standingData[x].points);
						currentRow['Pacific'] = parseInt(standingData[x].ROW);
						topDivs[3]=(standingData[x].team_name);

					}
					else if(parseInt(currentHigh['Pacific']) == (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						if(parseInt(currentRow['Pacific']) < parseInt(standingData[x].ROW)){
						currentHigh['Pacific'] = parseInt(standingData[x].points);
						currentRow['Pacific'] = parseInt(standingData[x].ROW);
						topDivs[3]=(standingData[x].team_name);
						}
					}
				}
				if( standingData[x].divison_name == 'Northwest'){
					if(parseInt(currentHigh['Northwest']) < (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						currentHigh['Northwest'] = parseInt(standingData[x].points);
						currentRow['Northwest'] = parseInt(standingData[x].ROW);
						topDivs[4]=(standingData[x].team_name);

					}
					else if(parseInt(currentHigh['Northwest']) == (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						if(parseInt(currentRow['Northwest']) < parseInt(standingData[x].ROW)){
						currentHigh['Northwest'] = parseInt(standingData[x].points);
						currentRow['Northwest'] = parseInt(standingData[x].ROW);
						topDivs[4]=(standingData[x].team_name);
						}
					}
				}
				if( standingData[x].divison_name == 'Central'){
					if(parseInt(currentHigh['Central']) < (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						currentHigh['Central'] = parseInt(standingData[x].points);
						currentRow['Central'] = parseInt(standingData[x].ROW);
						topDivs[5]=(standingData[x].team_name);

					}
					else if(parseInt(currentHigh['Central']) == (parseInt(standingData[x].points)/parseInt(standingData[x].GP))){
						if(parseInt(currentRow['Central']) < parseInt(standingData[x].ROW)){
						currentHigh['Central'] = parseInt(standingData[x].points);
						currentRow['Central'] = parseInt(standingData[x].ROW);
						topDivs[5]=(standingData[x].team_name);
						}
					}
				}
				else{
					otherTeams.push(standingData[x].team_name);

				}
			}
		}

		var	divConfContainer = document.createElement("div");
		for(var x = 0;x < standingData.length;x++){
			if( standingData[x].team_name in oc(topDivs)){
				var	divDivisionContainer = document.createElement("div");
				divDivisionContainer.className = "standTeamRow";
					var	divDivisionTitle = document.createElement("div");
					divDivisionTitle.className = "standTeamName col";
					txtDivisionTitle = document.createTextNode(standingData[x].team_name);
					divDivisionTitle.appendChild(txtDivisionTitle);
					var	divDivisionTitle2 = document.createElement("div");
					divDivisionTitle2.className = "standGP col";
					txtDivisionTitle2 = document.createTextNode(standingData[x].GP);
					divDivisionTitle2.appendChild(txtDivisionTitle2);
					var	divDivisionTitle3 = document.createElement("div");
					divDivisionTitle3.className = "standPT col";
					txtDivisionTitle3 = document.createTextNode(standingData[x].points);
					divDivisionTitle3.appendChild(txtDivisionTitle3);

				divDivisionContainer.appendChild(divDivisionTitle);
				divDivisionContainer.appendChild(divDivisionTitle2);
				divDivisionContainer.appendChild(divDivisionTitle3);
				self.standingsContainer.appendChild(divDivisionContainer);
			}
			else{
				
				if( standingData[x].conference_name == conf){
					z++;
					//alert(conf);
					var	divDivisionContainer = document.createElement("div");
					divDivisionContainer.className = "standTeamRow";
					
					if(z==5)divDivisionContainer.className += " eighth";
						var	divDivisionTitle = document.createElement("div");
						divDivisionTitle.className = "standTeamName col";
						txtDivisionTitle = document.createTextNode(standingData[x].team_name);
						divDivisionTitle.appendChild(txtDivisionTitle);
						var	divDivisionTitle2 = document.createElement("div");
						divDivisionTitle2.className = "standGP col";
						txtDivisionTitle2 = document.createTextNode(standingData[x].GP);
						divDivisionTitle2.appendChild(txtDivisionTitle2);
						var	divDivisionTitle3 = document.createElement("div");
						divDivisionTitle3.className = "standPT col";
						txtDivisionTitle3 = document.createTextNode(standingData[x].points);
						divDivisionTitle3.appendChild(txtDivisionTitle3);
						var	divDivisionClear= document.createElement("div");
						divDivisionClear.className = "clear";
					divDivisionContainer.appendChild(divDivisionTitle);
					divDivisionContainer.appendChild(divDivisionTitle2);
					divDivisionContainer.appendChild(divDivisionTitle3);
					divDivisionContainer.appendChild(divDivisionClear);
					divConfContainer.appendChild(divDivisionContainer);
				}
			}
		}
		self.standingsContainer.appendChild(divConfContainer);

	}
	this.drawLeaderList = function(type){
		var self = DrawStats;
		self.drawPlayerLeaders(self.topLeaderPoints,StatsDaily.data.leaders[type].points);
		self.drawPlayerLeaders(self.topLeaderGoals,StatsDaily.data.leaders[type].goals);
		self.drawPlayerLeaders(self.topLeaderAssists,StatsDaily.data.leaders[type].assists);
		self.drawPlayerLeaders(self.topLeaderPIM,StatsDaily.data.leaders[type].pim);
		self.drawPlayerLeaders(self.topLeaderP_M,StatsDaily.data.leaders[type].p_m);
	}
	this.drawGoalieLeaderList = function(type){
		var self = DrawStats;
		self.drawPlayerLeaders(self.topLeaderWins,StatsDaily.data.leaders[type].wins);
		self.drawPlayerLeaders(self.topLeaderSVP,StatsDaily.data.leaders[type].svp);
		self.drawPlayerLeaders(self.topLeaderGAA,StatsDaily.data.leaders[type].gaa);
		self.drawPlayerLeaders(self.topLeaderSO,StatsDaily.data.leaders[type].so);
	}
	this.drawGameList = function(container,data){
		var self = DrawStats;

		//alert($(container).attr('id'));
		//build and draw game list
		container = document.getElementById(container);
		if (data.length >0)
		{
			container.removeChild(container.firstChild);
		}
		ul = document.createElement("ul")
			//alert(data.length)
		//for (var game in data) {
		for(var x = 0;x < data.length;x++){
				var li = document.createElement("li");
				li.id = data[x].gameID;
				var gameID = data[x].gameID;
				if (data[x].gameID == activegame)
				{
					li.className += " sel";
				}

				if(data[x].home_score==null)
					var txt_home_score = '';
				else var txt_home_score = data[x].home_score;
				if(data[x].away_score==null)
					var txt_away_score = '';
				else var txt_away_score = data[x].away_score;
				//Home

				hmSpan = document.createElement("span");
				hmSpan.className += " scoresHome";
				txtHmSpan = document.createTextNode(data[x].home_team+' ');
				hmSpan.appendChild(txtHmSpan);
				hmSpanScore = document.createElement("span");
				txtHmSpanScore = document.createTextNode(txt_home_score);
				hmSpanScore.appendChild(txtHmSpanScore);
				hmSpan.appendChild(hmSpanScore);
				//Seperator

				spanSep = document.createElement("span");
				spanSep.className += " scoresSpan";
				txtSpanSep = document.createTextNode('-');
				spanSep.appendChild(txtSpanSep);

				//Away

				awSpan = document.createElement("span");
				awSpan.className += " scoresAway";
				txtAwSpan = document.createTextNode(' '+data[x].away_team);

				awSpanScore = document.createElement("span");
				txtAwSpanScore = document.createTextNode(txt_away_score);
				awSpanScore.appendChild(txtAwSpanScore);
				awSpan.appendChild(awSpanScore);
				awSpan.appendChild(txtAwSpan);

				li.appendChild(hmSpan);
				li.appendChild(spanSep);
				li.appendChild(awSpan);

				$(li).click(function () {
					self.updCurGame(this);
					activegame = gameID;
				}); //close click

				ul.appendChild(li);
		
		}
		container.appendChild(ul);
	}
	this.updCurGame = function(e){
		var self = DrawStats;
		self.divActiveGame = document.getElementById('activeGameInfo');

		self.scTop = document.getElementById('scTop');
		self.scPeriods = document.getElementById('scPeriods');
		self.scScroll = document.getElementById('scScroll');


		var currentId = $(e).attr('id');
		var ajaxURL ='';
		ajaxURL = self.ajaxGameUpd;
		
		$(e).toggleClass(function() {
			$(this).parent().parent().parent().parent().find('li').removeClass('sel');
		activegame = currentId;
		});
				//find data object
		//alert(StatsDaily.data.gameData[currentId].gameID_sched);
		$(e).addClass('sel')

		//Create DOM layout - Middle First


		//Now do scPeriods (This shoudl be First to calc period totals)
		//div id='sctop'
		var divScPeriods = document.createElement("div");
		divScPeriods.id = 'scPeriods';
		//scHomePeriods container
		var divScHomePeriods = document.createElement("div");
		divScHomePeriods.id = 'scHomePeriods';
		divScHomePeriods.className += ' ha_period';
		//scAwayPeriods container
		var divScAwayPeriods = document.createElement("div");
		divScAwayPeriods.id = 'scAwayPeriods';
		divScAwayPeriods.className += ' ha_period';
		var per1CountHome=0;
		var per2CountHome=0;
		var per3CountHome=0;
		var per1CountAway=0;
		var per2CountAway=0;
		var per3CountAway=0;
		var divPer1HomeContainer = document.createElement("div");
		divPer1HomeContainer.id = 'scHomePeriod1';
		divPer1HomeContainer.className = 'h_per';
		var divPer2HomeContainer = document.createElement("div");
		divPer2HomeContainer.id = 'scHomePeriod2';
		divPer2HomeContainer.className = 'h_per';
		var divPer3HomeContainer = document.createElement("div");
		divPer3HomeContainer.id = 'scHomePeriod3';
		divPer3HomeContainer.className = 'h_per';
		var divPer4HomeContainer = document.createElement("div");
		divPer4HomeContainer.id = 'scHomePeriod4';
		divPer4HomeContainer.className = 'h_per';
		var divPer5HomeContainer = document.createElement("div");
		divPer5HomeContainer.id = 'scHomePeriodSO';
		divPer5HomeContainer.className = 'h_per';
		var divPer1AwayContainer = document.createElement("div");
		divPer1AwayContainer.id = 'scAwayPeriod1';
		divPer1AwayContainer.className = 'a_per';
		var divPer2AwayContainer = document.createElement("div");
		divPer2AwayContainer.id = 'scAwayPeriod2';
		divPer2AwayContainer.className = 'a_per';
		var divPer3AwayContainer = document.createElement("div");
		divPer3AwayContainer.id = 'scAwayPeriod3';
		divPer3AwayContainer.className = 'a_per';
		var divPer4AwayContainer = document.createElement("div");
		divPer4AwayContainer.id = 'scAwayPeriod4';
		divPer4AwayContainer.className = 'a_per';
		var divPer5AwayContainer = document.createElement("div");
		divPer5AwayContainer.id = 'scAwayPeriodSO';
		divPer5AwayContainer.className = 'a_per';

		var divPer1GoalWrapper = document.createElement("div");		
		var divAwayPer1GoalWrapper = document.createElement("div");		
		var divPer2GoalWrapper = document.createElement("div");		
		var divAwayPer2GoalWrapper = document.createElement("div");		
		var divPer3GoalWrapper = document.createElement("div");		
		var divAwayPer3GoalWrapper = document.createElement("div");		
		var divPer4GoalWrapper = document.createElement("div");		
		var divAwayPer4GoalWrapper = document.createElement("div");		
		//alert(StatsDaily.data.gameData[currentId].goalsAssists.length);


		for(var x=0;x<StatsDaily.data.gameData[currentId].goalsAssists.length;x++){

			switch(StatsDaily.data.gameData[currentId].goalsAssists[x].period)
			{
				case '1':
				
					if(StatsDaily.data.gameData[currentId].home_team_id == StatsDaily.data.gameData[currentId].goalsAssists[x].scoring_team_id){
						per1CountHome++;
						var divPer1GoalScorer = document.createElement("div");
						divPer1GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divPer1GoalScorer.appendChild(text);
						var divPer1GoalAssists = document.createElement("div");
						divPer1GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divPer1GoalAssists.appendChild(text);
						divPer1GoalWrapper.appendChild(divPer1GoalScorer);
						divPer1GoalWrapper.appendChild(divPer1GoalAssists);
					}else{
						per1CountAway++;
						var divAwayPer1GoalScorer = document.createElement("div");
						divAwayPer1GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divAwayPer1GoalScorer.appendChild(text);
						var divAwayPer1GoalAssists = document.createElement("div");
						divAwayPer1GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divAwayPer1GoalAssists.appendChild(text);
						divAwayPer1GoalWrapper.appendChild(divAwayPer1GoalScorer);
						divAwayPer1GoalWrapper.appendChild(divAwayPer1GoalAssists);
					}
					break;
				case '2':
					if(StatsDaily.data.gameData[currentId].home_team_id == StatsDaily.data.gameData[currentId].goalsAssists[x].scoring_team_id){
						per2CountHome++;
						var divPer2GoalScorer = document.createElement("div");
						divPer2GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divPer2GoalScorer.appendChild(text);
						var divPer2GoalAssists = document.createElement("div");
						divPer2GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divPer2GoalAssists.appendChild(text);
						divPer2GoalWrapper.appendChild(divPer2GoalScorer);
						divPer2GoalWrapper.appendChild(divPer2GoalAssists);
					}
					else{
						per2CountAway++;
						var divAwayPer2GoalScorer = document.createElement("div");
						divAwayPer2GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divAwayPer2GoalScorer.appendChild(text);
						var divAwayPer2GoalAssists = document.createElement("div");
						divAwayPer2GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divAwayPer2GoalAssists.appendChild(text);
						divAwayPer2GoalWrapper.appendChild(divAwayPer2GoalScorer);
						divAwayPer2GoalWrapper.appendChild(divAwayPer2GoalAssists);
					}
					break;
				case '3':
					if(StatsDaily.data.gameData[currentId].home_team_id == StatsDaily.data.gameData[currentId].goalsAssists[x].scoring_team_id){
						per3CountHome++;
						var divPer3GoalScorer = document.createElement("div");
						divPer3GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divPer3GoalScorer.appendChild(text);
						var divPer3GoalAssists = document.createElement("div");
						divPer3GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divPer3GoalAssists.appendChild(text);
						divPer3GoalWrapper.appendChild(divPer3GoalScorer);
						divPer3GoalWrapper.appendChild(divPer3GoalAssists);
					}
					else{
						per3CountAway++;
						var divAwayPer3GoalScorer = document.createElement("div");
						divAwayPer3GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divAwayPer3GoalScorer.appendChild(text);
						var divAwayPer3GoalAssists = document.createElement("div");
						divAwayPer3GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divAwayPer3GoalAssists.appendChild(text);
						divAwayPer3GoalWrapper.appendChild(divAwayPer3GoalScorer);
						divAwayPer3GoalWrapper.appendChild(divAwayPer3GoalAssists);
					}
					break;
				case 'OT':
					if(StatsDaily.data.gameData[currentId].home_team_id == StatsDaily.data.gameData[currentId].goalsAssists[x].scoring_team_id){
						var divPer4GoalScorer = document.createElement("div");
						divPer4GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divPer4GoalScorer.appendChild(text);
						var divPer4GoalAssists = document.createElement("div");
						divPer4GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divPer4GoalAssists.appendChild(text);
						divPer4GoalWrapper.appendChild(divPer4GoalScorer);
						divPer4GoalWrapper.appendChild(divPer4GoalAssists);
					}		
					else{
						var divAwayPer4GoalScorer = document.createElement("div");
						divAwayPer4GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divAwayPer4GoalScorer.appendChild(text);
						var divAwayPer4GoalAssists = document.createElement("div");
						divAwayPer4GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divAwayPer4GoalAssists.appendChild(text);
						divAwayPer4GoalWrapper.appendChild(divAwayPer4GoalScorer);
						divAwayPer4GoalWrapper.appendChild(divAwayPer4GoalAssists);
					}
					break;
				case 'SO':
					if(StatsDaily.data.gameData[currentId].home_team_id == StatsDaily.data.gameData[currentId].goalsAssists[x].scoring_team_id){
						var divPer5GoalWrapper = document.createElement("div");		
						var divPer5GoalScorer = document.createElement("div");
						divPer5GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted);
						divPer5GoalScorer.appendChild(text);
						divPer5GoalWrapper.appendChild(divPer5GoalScorer);
					}
					else{
						var divAwayPer5GoalWrapper = document.createElement("div");		
						var divAwayPer5GoalScorer = document.createElement("div");
						divAwayPer5GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted);
						divAwayPer5GoalScorer.appendChild(text);
						divAwayPer5GoalWrapper.appendChild(divAwayPer5GoalScorer);
					}
					break;
				default:
					if(StatsDaily.data.gameData[currentId].home_team_id == StatsDaily.data.gameData[currentId].goalsAssists[x].scoring_team_id){
						var divPer4GoalScorer = document.createElement("div");
						divPer4GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divPer4GoalScorer.appendChild(text);
						var divPer4GoalAssists = document.createElement("div");
						divPer4GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divPer4GoalAssists.appendChild(text);
						divPer4GoalWrapper.appendChild(divPer4GoalScorer);
						divPer4GoalWrapper.appendChild(divPer4GoalAssists);
					}		
					else{
						var divAwayPer4GoalScorer = document.createElement("div");
						divAwayPer4GoalScorer.className = 'scScorer';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].full_name_frmatted+' ('+StatsDaily.data.gameData[currentId].goalsAssists[x].time+')');
						divAwayPer4GoalScorer.appendChild(text);
						var divAwayPer4GoalAssists = document.createElement("div");
						divAwayPer4GoalAssists.className = 'scAssists';
						var text = document.createTextNode(StatsDaily.data.gameData[currentId].goalsAssists[x].assists);
						divAwayPer4GoalAssists.appendChild(text);
						divAwayPer4GoalWrapper.appendChild(divAwayPer4GoalScorer);
						divAwayPer4GoalWrapper.appendChild(divAwayPer4GoalAssists);
					}
			}
		}
		if(typeof divPer1GoalWrapper != 'undefined'){
			divPer1HomeContainer.appendChild(divPer1GoalWrapper);
			divScHomePeriods.appendChild(divPer1HomeContainer);
		}
		if(typeof divPer2GoalWrapper != 'undefined'){
			divPer2HomeContainer.appendChild(divPer2GoalWrapper);
			divScHomePeriods.appendChild(divPer2HomeContainer);
		}
		if(typeof divPer3GoalWrapper != 'undefined'){
			divPer3HomeContainer.appendChild(divPer3GoalWrapper);
			divScHomePeriods.appendChild(divPer3HomeContainer);
		}
		if(typeof divPer4GoalWrapper != 'undefined'){
			divPer4HomeContainer.appendChild(divPer4GoalWrapper);
			divScHomePeriods.appendChild(divPer4HomeContainer);
		}
		if(typeof divPer5GoalWrapper != 'undefined'){
			divPer5HomeContainer.appendChild(divPer5GoalWrapper);
			divScHomePeriods.appendChild(divPer5HomeContainer);
		}
		if(typeof divAwayPer1GoalWrapper != 'undefined'){
			divPer1AwayContainer.appendChild(divAwayPer1GoalWrapper);
			divScAwayPeriods.appendChild(divPer1AwayContainer);
		}
		if(typeof divAwayPer2GoalWrapper != 'undefined'){
			divPer2AwayContainer.appendChild(divAwayPer2GoalWrapper);
			divScAwayPeriods.appendChild(divPer2AwayContainer);
		}
		if(typeof divAwayPer3GoalWrapper != 'undefined'){
			divPer3AwayContainer.appendChild(divAwayPer3GoalWrapper);
			divScAwayPeriods.appendChild(divPer3AwayContainer);
		}
		if(typeof divAwayPer4GoalWrapper != 'undefined'){
			divPer4AwayContainer.appendChild(divAwayPer4GoalWrapper);
			divScAwayPeriods.appendChild(divPer4AwayContainer);
		}
		if(typeof divAwayPer5GoalWrapper != 'undefined'){
			divPer5AwayContainer.appendChild(divAwayPer5GoalWrapper);
			divScAwayPeriods.appendChild(divPer5AwayContainer);
		}
		
		divScPeriods.appendChild(divScHomePeriods);
		divScPeriods.appendChild(divScAwayPeriods);
		//add clear
		var divClear = document.createElement("div");
		divClear.className = "clear";
		divScPeriods.appendChild(divClear);


		//div id='sctop'
		var divScTop = document.createElement("div");
		divScTop.id = 'scTop';
		var divScHmContainer = document.createElement("div");
		divScHmContainer.id = 'scHomeTeam';
		divScHmContainer.className += ' scTopRow';
		var divScTopHmTeam = document.createElement("div");
		divScTopHmTeam.className += " scTeam";
		var text = document.createTextNode(StatsDaily.data.gameData[currentId].homeAbbr);
		divScTopHmTeam.appendChild(text);
		var divScTopHmLogo = document.createElement("div");
		divScTopHmLogo.className += " scLogo";
		var imgHomeLogo = document.createElement("img");
		imgHomeLogo.src= self.imagesURL+StatsDaily.data.gameData[currentId].homeIMG;
		//append img to logo container div
		divScTopHmLogo.appendChild(imgHomeLogo);
		//append team txt and logo to hoem container div
		divScHmContainer.appendChild(divScTopHmTeam);
		divScHmContainer.appendChild(divScTopHmLogo);

		//home Scores contianer
		var divScHmScoresContainer = document.createElement("div");
		divScHmScoresContainer.id = 'scHomeScores';
		divScHmScoresContainer.className += ' scTopRow';
        // creates a <table> element and a <tbody> element
        var tbl     = document.createElement("table");
        var tblBody = document.createElement("tbody");

        // creating all cells
        for (var j = 0; j <= 2; j++) {
            // creates a table row
            var row = document.createElement("tr");

            for (var i = 0; i <= 0; i++) {
                // Create a <td> element and a text node, make the text
                // node the contents of the <td>, and put the <td> at
                // the end of the table row
                var cell = document.createElement("td");
				cell.className += ' score';
				var cellText = '0';
				switch(j)
				{
					case 0:
						cellText = document.createTextNode(per1CountHome);
						break;
					case 1:
						cellText = document.createTextNode(per2CountHome);
						break;
					case 2:
						cellText = document.createTextNode(per3CountHome);
						break;
				}
                cell.appendChild(cellText);
                row.appendChild(cell);
            }

            // add the row to the end of the table body
            tblBody.appendChild(row);
        }

        // put the <tbody> in the <table>
        tbl.appendChild(tblBody);
        // appends <table> into divScHmScoresContainer
        divScHmScoresContainer.appendChild(tbl);

		//score div container
		var divScScoreContainer = document.createElement("div");
		divScScoreContainer.id = 'scScore';
		divScScoreContainer.className += ' scTopRow';
		var divScScore = document.createElement("div");
		if(StatsDaily.data.gameData[currentId].home_score == null)
			hScore = 0;
		else hScore = StatsDaily.data.gameData[currentId].home_score;
		if(StatsDaily.data.gameData[currentId].away_score == null)
			aScore = 0;
		else aScore = StatsDaily.data.gameData[currentId].away_score;
		txtScores = document.createTextNode(hScore+'-'+aScore);
		divScScore.appendChild(txtScores);
		var divScStatus = document.createElement("div");
		divScStatus.className += ' gameStatus';
		if(StatsDaily.data.gameData[currentId].gameStatus != null)
		txtStatus = document.createTextNode(StatsDaily.data.gameData[currentId].gameStatus+' '+StatsDaily.data.gameData[currentId].html_extraPeriods);
		else
			txtStatus = document.createTextNode(StatsDaily.data.gameData[currentId].time);
		divScStatus.appendChild(txtStatus);
		divScScoreContainer.appendChild(divScScore);
		divScScoreContainer.appendChild(divScStatus);

		//away period container
		//home Scores contianer
		var divScAwScoresContainer = document.createElement("div");
		divScAwScoresContainer.id = 'scAwayScores';
		divScAwScoresContainer.className += ' scTopRow';
        // creates a <table> element and a <tbody> element
        var tbl     = document.createElement("table");
        var tblBody = document.createElement("tbody");

        // creating all cells
        for (var j = 0; j <= 2; j++) {
            // creates a table row
            var row = document.createElement("tr");

            for (var i = 0; i <= 0; i++) {
                // Create a <td> element and a text node, make the text
                // node the contents of the <td>, and put the <td> at
                // the end of the table row
                var cell = document.createElement("td");
				cell.className += ' score';
				var cellText = '0';
				switch(j)
				{
					case 0:
						cellText = document.createTextNode(per1CountAway);
						break;
					case 1:
						cellText = document.createTextNode(per2CountAway);
						break;
					case 2:
						cellText = document.createTextNode(per3CountAway);
						break;
				}
				cell.appendChild(cellText);
                row.appendChild(cell);
            }

            // add the row to the end of the table body
            tblBody.appendChild(row);
        }

        // put the <tbody> in the <table>
        tbl.appendChild(tblBody);
        // appends <table> into divScHmScoresContainer
        divScAwScoresContainer.appendChild(tbl);

		//div id='sctop'

		var divScAwContainer = document.createElement("div");
		divScAwContainer.id = 'scAwayTeam';
		divScAwContainer.className += ' scTopRow';
		var divScTopAwTeam = document.createElement("div");
		divScTopAwTeam.className += " scTeam";
		var text = document.createTextNode(StatsDaily.data.gameData[currentId].awayAbbr);
		divScTopAwTeam.appendChild(text);

		var divScTopAwLogo = document.createElement("div");
		divScTopAwLogo.className += " scLogo";
		var imgAwayLogo = document.createElement("img");
		imgAwayLogo.src= self.imagesURL+StatsDaily.data.gameData[currentId].awayIMG;
		//append img to logo container div
		divScTopAwLogo.appendChild(imgAwayLogo);
		//append team txt and logo to hoem container div
		divScAwContainer.appendChild(divScTopAwTeam);
		divScAwContainer.appendChild(divScTopAwLogo);

		//Append all the rest to sctop
		divScTop.appendChild(divScHmContainer);
		divScTop.appendChild(divScHmScoresContainer);
		divScTop.appendChild(divScScoreContainer);
		divScTop.appendChild(divScAwScoresContainer);
		divScTop.appendChild(divScAwContainer);

		//add clear
		var divClear = document.createElement("div");
		divClear.className = "clear";
		divScTop.appendChild(divClear);

		//Scroller Div scScroll
		var divScroller = document.createElement("div");
		divScroller.id = 'scScroll';
		//scHomePeriods container
		var divScrollerHome = document.createElement("div");
		divScrollerHome.id = 'scHomeScroll';
		divScrollerHome.className += ' ha_period';
		//scAwayPeriods container
		var divScrollerAway = document.createElement("div");
		divScrollerAway.id = 'scAwayScroll';
		divScrollerAway.className += ' ha_period';
		//Add buttons to each scroller (Home)
		var aScrollerHomeUp = document.createElement("a");
		aScrollerHomeUp.id = 'hmScrollUp';
		aScrollerHomeUp.className += ' ha_ScrollUp';
		aScrollerHomeUp.href = '#';
		var aScrollerHomeDown = document.createElement("a");
		aScrollerHomeDown.id = 'hmScrollDown';
		aScrollerHomeDown.className += ' ha_ScrollDown';
		aScrollerHomeDown.href = '#';
		
		var spanScrollerHome = document.createElement("span");
		var text = document.createTextNode('Up');
		spanScrollerHome.appendChild(text);
		aScrollerHomeUp.appendChild(spanScrollerHome);
		var spanScrollerHomeDown = document.createElement("span");
		var text = document.createTextNode('Down');
		spanScrollerHomeDown.appendChild(text);
		aScrollerHomeDown.appendChild(spanScrollerHomeDown);
		divScrollerHome.appendChild(aScrollerHomeUp);
		divScrollerHome.appendChild(aScrollerHomeDown);
		divScroller.appendChild(divScrollerHome);

		//away Scroller

		var aScrollerAwayUp = document.createElement("a");
		aScrollerAwayUp.id = 'awScrollUp';
		aScrollerAwayUp.className += ' ha_ScrollUp';
		aScrollerAwayUp.href = '#';
		var aScrollerAwayDown = document.createElement("a");
		aScrollerAwayDown.id = 'awScrollDown';
		aScrollerAwayDown.className += ' ha_ScrollDown';
		aScrollerAwayDown.href = '#';
		var spanScrollerAway = document.createElement("span");
		var text = document.createTextNode('Up');
		spanScrollerAway.appendChild(text);
		aScrollerAwayUp.appendChild(spanScrollerAway);
		var spanScrollerAwayDown = document.createElement("span");
		var text = document.createTextNode('Down');
		spanScrollerAwayDown.appendChild(text);
		aScrollerAwayDown.appendChild(spanScrollerAwayDown);
		divScrollerAway.appendChild(aScrollerAwayUp);
		divScrollerAway.appendChild(aScrollerAwayDown);
		divScroller.appendChild(divScrollerAway);

		//Replace scTop with new data
		self.divActiveGame.replaceChild(divScTop,self.scTop);
		//Append MIddle
		self.divActiveGame.replaceChild(divScPeriods,self.scPeriods);
		//replace bottom
		self.divActiveGame.replaceChild(divScroller,self.scScroll);
		//divScTopHmTeam.appendChild(text);
		self.attachScrollListener();
	}
	this.attachScrollListener = function(){
		if($("#scHomePeriods").attr("scrollHeight") > $("#scHomePeriods").height()){
			$("#hmScrollDown").click(function () {
				var maxScroll = $("#scHomePeriods").attr("scrollHeight") -
				$("#scHomePeriods").height();
				$('#scHomePeriods').animate({ scrollTop: maxScroll }); 
				return false;
			});
			$("#hmScrollUp").click(function () {
				var maxScroll = $("#scHomePeriods").attr("scrollHeight") -
				$("#scHomePeriods").height();
				$('#scHomePeriods').animate({ scrollTop: 0 }); 
				return false;
			});
		}
		else{ 
			$("#hmScrollDown").addClass('ha_ScrollDownNA').removeClass('ha_ScrollDown');
			$("#hmScrollUp").addClass('ha_ScrollUpNA').removeClass('ha_ScrollUp'); 
			$("#hmScrollDown").click(function () {	return false;});
			$("#hmScrollUp").click(function () {return false;});
		}
		if($("#scAwayPeriods").attr("scrollHeight") > $("#scAwayPeriods").height()){
			$("#awScrollDown").click(function () {
				var maxScroll = $("#scAwayPeriods").attr("scrollHeight") -
				$("#scAwayPeriods").height();
				$('#scAwayPeriods').animate({ scrollTop: maxScroll }); 
				return false;
			});
			$("#awScrollUp").click(function () {
				var maxScroll = $("#scAwayPeriods").attr("scrollHeight") -
				$("#scAwayPeriods").height();
				$('#scAwayPeriods').animate({ scrollTop: 0 }); 
				return false;
			});
		}
		else{ 
			$("#awScrollDown").addClass('ha_ScrollDownNA').removeClass('ha_ScrollDown');
			$("#awScrollUp").addClass('ha_ScrollUpNA').removeClass('ha_ScrollUp'); 
			$("#awScrollDown").click(function () {	return false;});
			$("#awScrollUp").click(function () {return false;});
		}
	}
	this.drawPlayerLeaders = function(container,data){
		var self = DrawStats;
		self.contPoints = document.getElementById(container);
		while (self.contPoints.firstChild) {
			self.contPoints.removeChild(self.contPoints.firstChild);
		}
		//alert(self.contPoints);
		ul = document.createElement("ul")
		for(var x = 0;x < data.length;x++){
			//Points
			var li = document.createElement("li");
			var	divLeadName = document.createElement("div");
			divLeadName.className += " topSeasonName";
			txtLeadName = document.createTextNode(data[x].full_name);
			var	divLeadNum = document.createElement("div");
			divLeadNum.className += " topSeasonNum";
			txtLeadNum = document.createTextNode(data[x].stat);
			divLeadName.appendChild(txtLeadName);
			divLeadNum.appendChild(txtLeadNum);
			li.appendChild(divLeadName);
			li.appendChild(divLeadNum);
			ul.appendChild(li);

			self.contPoints.appendChild(ul);
		//Replace scTop with new data
		}
		
	}
}
var StatsDaily=  new function(){
	this.data = [];
	this.reqHand = null;
	this.reqDraw = DrawStats;
	this.init =  function(){
		var self = StatsDaily;
		self.targetURL = "/home/fetchData";
		self.getDate();
		self.toggleAppStatus(true);
		self.cleanup();
	};
	this.toggleAppStatus =  function(stopped){
		var self = StatsDaily;
		self.toggleButton(stopped);
		self.toggleStatusMessage(stopped);
		self.updateData();
		var reqHand = setInterval(self.updateData,300000)
	};
	this.toggleButton = function(stopped){
		var self = StatsDaily;
		//var buttonDiv = $('buttonArea');
		//stump for button
	};
	this.toggleStatusMessage = function(stopped){
		var msg;
		//var buttonDiv = $('currentAppState');
		//stump for toggleStatusMessage
	};
	this.reqServerStop = function(){
		var self = StatsDaily;
		if(self.stopRequests()){
			self.toggleAppStatus(true);
		}
		self.reqStatus.stopProc(false);
	};
	this.stopRequests = function(){
		var self = StatsDaily;
		clearInterval(self.reqHand);
		return true;
	}
	this.getDate = function(){
		var self = StatsDaily;

		//this function sets the current date
		var currentTime = new Date()
		var month = currentTime.getMonth() + 1;
		var day = currentTime.getDate();
		var year = currentTime.getFullYear();
		self.today = year + "-" + month + "-" + day;
		self.secondary = year + "-" + month + "-" + (day-1);
		//alert(this.today);
	};
	this.updateData = function(){
		var self = StatsDaily;
		//this function updates the JSON array at an interval
		$.ajax({
		  type: "POST",
		  url: "/home/fetchData",
		  cache: false,
		  data: {today :self.today, secondary :self.secondary},
		  success: function(json){
			  self.data = eval('(' + json + ')');
			  DrawStats.init();
			//self.data = json;
		  }
		});
		self.parseData();
	};
	this.parseData = function(){
		//this function cycles through the JSON array and updates the HTML
		//Also, it will need to attach onClick events on LI items and scrollers
	};
	this.cleanup = function(){
		var self = StatsDaily;
		self.reqDraw.cleanup();
		self.reqDraw = null;
	}
}

window.onunload = StatsDaily.cleanup;

$(document).ready(function(){
		if($("#scHomePeriods").attr("scrollHeight") > $("#scHomePeriods").height()){
			$("#hmScrollDown").click(function () {
				var maxScroll = $("#scHomePeriods").attr("scrollHeight") -
				$("#scHomePeriods").height();
				$('#scHomePeriods').animate({ scrollTop: maxScroll }); 
				return false;
			});
			$("#hmScrollUp").click(function () {
				var maxScroll = $("#scHomePeriods").attr("scrollHeight") -
				$("#scHomePeriods").height();
				$('#scHomePeriods').animate({ scrollTop: 0 }); 
				return false;
			});
		}
		else{ $("#hmScrollDown").addClass('ha_ScrollDownNA').removeClass('ha_ScrollDown');$("#hmScrollUp").addClass('ha_ScrollUpNA').removeClass('ha_ScrollUp'); }
		if($("#scAwayPeriods").attr("scrollHeight") > $("#scAwayPeriods").height()){
			$("#awScrollDown").click(function () {
				var maxScroll = $("#scAwayPeriods").attr("scrollHeight") -
				$("#scAwayPeriods").height();
				$('#scAwayPeriods').animate({ scrollTop: maxScroll }); 
				return false;
			});
			$("#awScrollUp").click(function () {
				var maxScroll = $("#scAwayPeriods").attr("scrollHeight") -
				$("#scAwayPeriods").height();
				$('#scAwayPeriods').animate({ scrollTop: 0 }); 
				return false;
			});
		}
		else{ $("#awScrollDown").addClass('ha_ScrollDownNA').removeClass('ha_ScrollDown');$("#awScrollUp").addClass('ha_ScrollUpNA').removeClass('ha_ScrollUp'); }

	$('#frm_standSelDiv').val("1");
	$('#frm_standSelConf').val("Eastern");
    $('#smB1').click(function () {
		if($("#mTeams").is(':hidden')){
			$('ul#mLeaders').slideToggle('medium');
			$(this).find('.arrow').toggleClass('aup');
		}
		else{
			$("ul#mTeams").hide('fast');
			$("#smB2").find('.arrow').toggleClass('aup')
			$('ul#mLeaders').slideToggle('medium');
			$(this).find('.arrow').toggleClass('aup');
		}

    });
    $('#smB2').click(function () {
		if($("ul#mLeaders").is(':hidden')){
			$('ul#mTeams').slideToggle('medium');
			$(this).find('.arrow').toggleClass('aup');
		}
		else{
			$("ul#mLeaders").hide('fast');
			$("#smB1").find('.arrow').toggleClass('aup')
			$('ul#mTeams').slideToggle('medium');
			$(this).find('.arrow').toggleClass('aup');
		}
    });
	$('#smB3').click(function () {
		window.location.href = STATS_PAGE;
	});
	//Playoff Leader Events
    $('#plOptSeason').click(function () {
		$('#plOptTonight').removeClass('act');
		$('#plOptSeason').addClass("act");
		$('#plOptYesterday').removeClass("act");
		$('#plOptPlayoffs').removeClass("act");
		DrawStats.drawLeaderList('arrSeasLeaders');
    });
    $('#plOptTonight').click(function () {
		$('#plOptTonight').addClass('act');
		$('#plOptSeason').removeClass("act");
		$('#plOptYesterday').removeClass("act");
		$('#plOptPlayoffs').removeClass("act");
		DrawStats.drawLeaderList('arrTnLeaders');
    });
    $('#plOptYesterday').click(function () {
		$('#plOptTonight').removeClass('act');
		$('#plOptSeason').removeClass("act");
		$('#plOptPlayoffs').removeClass("act");
		$('#plOptYesterday').addClass("act");
		DrawStats.drawLeaderList('arrYestLeaders');
    });
    $('#plOptPlayoffs').click(function () {
		$('#plOptTonight').removeClass('act');
		$('#plOptSeason').removeClass("act");
		$('#plOptYesterday').removeClass("act");
		$('#plOptPlayoffs').addClass("act");
		DrawStats.drawLeaderList('arrPlayoffLeaders');
    });
	//Goalie Leader Events
    $('#glOptSeason').click(function () {
		$('#glOptTonight').removeClass('act');
		$('#glOptSeason').addClass("act");
		$('#glOptYesterday').removeClass("act");
		$('#glOptPlayoffs').removeClass("act");
		DrawStats.drawGoalieLeaderList('arrGlSeasLeaders');
    });
    $('#glOptTonight').click(function () {
		$('#glOptTonight').addClass('act');
		$('#glOptSeason').removeClass("act");
		$('#glOptYesterday').removeClass("act");
		$('#glOptPlayoffs').removeClass("act");
		DrawStats.drawGoalieLeaderList('arrGlTnLeaders');
    });
    $('#glOptYesterday').click(function () {
		$('#glOptTonight').removeClass('act');
		$('#glOptSeason').removeClass("act");
		$('#glOptPlayoffs').removeClass("act");
		$('#glOptYesterday').addClass("act");
		DrawStats.drawGoalieLeaderList('arrGlYestLeaders');
    });
    $('#glOptPlayoffs').click(function () {
		$('#glOptTonight').removeClass('act');
		$('#glOptSeason').removeClass("act");
		$('#glOptYesterday').removeClass("act");
		$('#glOptPlayoffs').addClass("act");
		DrawStats.drawGoalieLeaderList('arrGlPlayoffLeaders');
    });
	//Standing Events
    $('#standSelConf').click(function () {
		$('#frm_standSelDiv').val("0");
		$('#standSelConf').addClass('sel');
		$('#standSelDiv').removeClass('sel');
		DrawStats.drawStandings('season',$('#frm_standSelConf').val(), 0);
    });
    $('#standSelDiv').click(function () {
		$('#frm_standSelDiv').val("1");
		$('#standSelDiv').addClass('sel');
		$('#standSelConf').removeClass('sel');
		DrawStats.drawStandings('season',$('#frm_standSelConf').val(), 1);
    });
    $('#standSelWest').click(function () {
		$('#frm_standSelConf').val("Western");
		$('#standSelWest').addClass('act');
		$('#standSelEast').removeClass('act');
		DrawStats.drawStandings('season','Western', $('#frm_standSelDiv').val());
    });
    $('#standSelEast').click(function () {
		$('#frm_standSelConf').val("Eastern");
		$('#standSelEast').addClass('act');
		$('#standSelWest').removeClass('act');
		DrawStats.drawStandings('season','Eastern', $('#frm_standSelDiv').val());
    });
});
// GENERAL SORT FUNCTION:
// Sort on single or multi-column arrays.
// Sort set up for six colums, in order of u,v,w,x,y,z.   For single columns (single-dimensioned array), omit all u,v....
// Sort will continue only as far as the specified number of columns: "w,x" only sorts on two columns, etc.
// Sort will place numbers before strings, and swap until all columns are in ascending order.
// Sorter algorithm:
// Is result of a-b NaN?.  Then one or both is text.
//   Are both text?  Then do a general swap. Set var 'swap' to 1:0:-1, accordingly: 1 push up list, -1 push down.
//   Else one is text, the other a number.  Therefore, is 'a' text?  Then push up, else 'b' is text - push 'a' down.
// Else both are numbers.
// return result in var 'swap'.
// To do multi-columns, repeat the operations for each column.
// To do ascending.descending, asending, etc columns, see the code further down the page.

function SortIt(TheArr,u,v,w,x,y,z){

  if(u==undefined){TheArr.sort(Sortsingle);} // this is a simple array, not multi-dimensional, ie, SortIt(TheArr);
  else{TheArr.sort(Sortmulti);}

  function Sortsingle(a,b){
    var swap=0;
    if(isNaN(a-b)){
      if((isNaN(a))&&(isNaN(b))){swap=(b<a)-(a<b);}
      else {swap=(isNaN(a)?1:-1);}
    }
    else {swap=(a-b);}
    return swap;
  }

 function Sortmulti(a,b){
  var swap=0;
    if(isNaN(a[u]-b[u])){
      if((isNaN(a[u]))&&(isNaN(b[u]))){swap=(b[u]<a[u])-(a[u]<b[u]);}
      else{swap=(isNaN(a[u])?1:-1);}
    }
    else{swap=(a[u]-b[u]);}
    if((v==undefined)||(swap!=0)){return swap;}
    else{
      if(isNaN(a[v]-b[v])){
        if((isNaN(a[v]))&&(isNaN(b[v]))){swap=(b[v]<a[v])-(a[v]<b[v]);}
        else{swap=(isNaN(a[v])?1:-1);}
      }
      else{swap=(a[v]-b[v]);}
      if((w==undefined)||(swap!=0)){return swap;}
      else{
        if(isNaN(a[w]-b[w])){
          if((isNaN(a[w]))&&(isNaN(b[w]))){swap=(b[w]<a[w])-(a[w]<b[w]);}
          else{swap=(isNaN(a[w])?1:-1);}
        }
        else{swap=(a[w]-b[w]);}
        if((x==undefined)||(swap!=0)){return swap;}
        else{
          if(isNaN(a[x]-b[x])){
            if((isNaN(a[x]))&&(isNaN(b[x]))){swap=(b[x]<a[x])-(a[x]<b[x]);}
            else{swap=(isNaN(a[x])?1:-1);}
          }
          else{swap=(a[x]-b[x]);}
          if((y==undefined)||(swap!=0)){return swap;}
          else{
            if(isNaN(a[y]-b[y])){
              if((isNaN(a[y]))&&(isNaN(b[y]))){swap=(b[y]<a[y])-(a[y]<b[y]);}
              else{swap=(isNaN(a[y])?1:-1);}
            }
            else{swap=(a[y]-b[y]);}
            if((z=undefined)||(swap!=0)){return swap;}
            else{
              if(isNaN(a[z]-b[z])){
                if((isNaN(a[z]))&&(isNaN(b[z]))){swap=(b[z]<a[z])-(a[z]<b[z]);}
                else{swap=(isNaN(a[z])?1:-1);}
              }
              else{swap=(a[z]-b[z]);}
              return swap;
} } } } } } }
function oc(a)
{
  var o = {};
  for(var i=0;i<a.length;i++)
  {
    o[a[i]]='';
  }
  return o;
}