<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : wforecast_town.xsl
    Created on : 08 February 2012, 00:13
    Author     : Justin Lewis Salmon
    Description:
        Stylesheet for coverting Weather Underground API forecast 
        weather XML to HTML.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>

    <xsl:template match="/">
        <table class="left" id="forecast">
            <xsl:for-each select="response/forecast/simpleforecast/forecastdays/forecastday">
                <tr>
                    <td>
                        <span class="day">
                            <xsl:value-of select="date/weekday"/>
                            <br />
                        </span>
                        <span class="date">
                            <span class="large"><xsl:value-of select="date/day"/>&#160;</span>
                            <xsl:value-of select="date/monthname"/>
                        </span>
                    </td>
                    <td>
                        <img class="img">
                            <xsl:attribute name="src">
                                <xsl:value-of select="icon_url"/>
                            </xsl:attribute>
                        </img>
                    </td>
                    <td>

                        <xsl:variable name="low" select="low/celsius"/>
                        <xsl:variable name="high" select="high/celsius"/>
                        <span class="temp">
                            <xsl:value-of select="($low + $high) div 2"/>&#176;C
                        </span>   
                        <br />
                        <span class="tiny">
                            <xsl:value-of select="low/celsius"/>&#176; | 
                            <xsl:value-of select="high/celsius"/>&#176;
                        </span>
                        <br />
                        <span class="cond">
                            <xsl:value-of select="conditions"/>
                        </span>
                    </td>
                    <td>
                        <span class="small">
                            Wind: 
                            <xsl:value-of select="avewind/mph"/> MPH
                            <xsl:value-of select="avewind/dir"/>
                            <br />
                        </span>
                        <hr />
                        <span class="small">
                            Humidity: 
                            <xsl:value-of select="avehumidity"/>%
                        </span>
                        <hr />
                        <span class="small">
                            QPF: 
                            <xsl:value-of select="qpf_allday"/>%
                        </span>
                    </td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
    
</xsl:stylesheet>
