<?xml version="1.0" encoding="UTF-8"?>

<!--
    Document   : wlive_town.xsl
    Created on : 08 February 2012, 00:13
    Author     : Justin Lewis Salmon
    Description:
        Stylesheet for coverting Weather Underground API live 
        weather XML to HTML.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>

    <xsl:template match="/">
        <table class="left">
            <xsl:for-each select="response/current_observation">
                <tr class="bigbox">
                    <td class="bigbox">
                        <img class="img-big">
                            <xsl:attribute name="src">
                                <xsl:value-of select="icon_url"/>
                            </xsl:attribute>
                        </img>
                    </td>
                    <td class="bigbox">
                        <span class="lastupd">
                            <xsl:value-of select="observation_time"/>
                        </span>
                        <br />
                        <br />
                        <span class="conditions">
                            <xsl:value-of select="weather"/>
                        </span>
                        <br />
                        <xsl:variable name="temp" select="temp_c"/>
                        <xsl:variable name="windchill" select="windchill_c"/>
                        <span class="temp">
                            <xsl:value-of select="temp_c"/>&#176;C 
                        </span>
                        (
                        <xsl:value-of select="temp_f"/>&#176;F)
                        <br />
                        <strong class="feels-like">
                            Feels like: 
                            <xsl:choose>
                                <xsl:when test="$windchill != 'NA'">
                                    <xsl:value-of select="round($temp - ($windchill div 2))"/>&#176;C
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="round($temp - 1)"/>&#176;C
                                </xsl:otherwise>
                            </xsl:choose>
                        </strong>
                        <br />
                        <br />
                        <span class="astro">
                            Sunrise: 
                            <xsl:value-of select="/response/moon_phase/sunrise/hour"/> :
                            <xsl:value-of select="/response/moon_phase/sunrise/minute"/>
                            Sunset: 
                            <xsl:value-of select="/response/moon_phase/sunset/hour"/> :
                            <xsl:value-of select="/response/moon_phase/sunset/minute"/>
                        </span>                            
                        <br />
                        <span class="astro">Moon illumination:
                            <xsl:value-of select="/response/moon_phase/percentIlluminated"/>%
                        </span>
                    </td>
                    <td class="satellite">
                        <img class="satellite">
                            <xsl:attribute name="src">
                                <xsl:value-of select="/response/satellite/image_url_ir4"/>
                            </xsl:attribute>
                        </img>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <span>Humidity: 
                            <xsl:value-of select="relative_humidity"/>
                        </span>
                        <br />
                        <hr />
                        <span>Wind: 
                            <xsl:value-of select="wind_string"/>
                        </span>
                        <br />
                        <hr />
                        <span>Pressure: 
                            <xsl:value-of select="pressure_mb"/> mb
                        </span>
                        <br />
                        <hr />
                        <span>Dewpoint: 
                            <xsl:value-of select="dewpoint_string"/>
                        </span>
                        <br />
                        <hr />
                        <span>Windchill: 
                            <xsl:value-of select="windchill_string"/>
                        </span>
                        <br />
                        <hr />
                        <span>Visibility: 
                            <xsl:value-of select="visibility_mi"/> mi
                        </span>
                        <br />
                        <hr />
                        <span>Today's precipitation: 
                            <xsl:value-of select="precip_today_string"/>
                        </span>
                        <br />
                    </td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
    
</xsl:stylesheet>
