<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <!--empty template suppresses this attribute-->
    <xsl:template match="@id" />
    <!--identity template copies everything forward by default-->
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
<xsl:template match="/">
<html>
<head><style>table {font-family: arial, sans-serif; border-collapse: collapse; width: 100%;} td, th {border: 1px solid #dddddd; text-align: left; padding: 3px;} tr:nth-child(even) {background-color: #dddddd;}"</style></head>
<title>Matches Overview</title>
<body>
<table width="100%" border="1">
	<THEAD>
		<TR>
			<TD width="7%"><B>Date</B></TD>
			<TD width="4%"><B>Time</B></TD>
			<TD width="7%"><B>League</B></TD>
			<TD width="2%"><B>Day</B></TD>
			<TD width="5%"><B>Match</B></TD>
			<TD width="20%"><B>TeamA</B></TD>
			<TD width="20%"><B>TeamB</B></TD>
			<TD width="15%"><B>Location</B></TD>
			<TD width="10%"><B>Youtube</B></TD>
			<TD width="6%"><B>LiveStat</B></TD>
			<TD width="1%"><B>Datecode</B></TD>
		</TR>
	</THEAD> 
	<TBODY>

	<xsl:for-each select="document/match">
		<xsl:sort select="Datecode"/>
		<xsl:if test="Date/@id !=''">
			<TR> 
				<TD width="7%"><xsl:value-of select="Date"/></TD> 
				<TD width="4%"><xsl:value-of select="Time"/></TD> 
				<TD width="7%"><xsl:value-of select="League"/></TD>
				<TD width="2%"><xsl:value-of select="Gameday"/></TD> 
				<TD width="8%"><xsl:value-of select="GameId"/></TD> 
				<TD width="20%"><xsl:value-of select="TeamA"/></TD>
				<TD width="20%"><xsl:value-of select="TeamB"/></TD>
				<TD width="15%"><xsl:value-of select="Location"/></TD>
				<TD width="10%"><xsl:value-of select="Youtube"/></TD>
				<TD width="6%"><xsl:value-of select="LiveStat"/></TD>
				<TD width="1%"><xsl:value-of select="Datecode"/></TD>   
			</TR>
		</xsl:if>
	</xsl:for-each>
  </TBODY>
</table>
</body>
</html>
</xsl:template>
</xsl:stylesheet>