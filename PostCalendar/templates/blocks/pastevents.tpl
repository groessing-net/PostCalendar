{* $Id: postcalendar_block_pastevents.htm 638 2010-06-30 22:14:17Z craigh $ *}
{pc_queued_events_notify}
{pc_pagejs_init}
<div class="postcalendar_block_pastevents">
{counter start=0 assign=eventcount}
{pc_sort_events var="S_EVENTS" sort="time" order="desc" value=$A_EVENTS}
{foreach name=dates item=events key=date from=$S_EVENTS}
    {foreach name=eventloop key=id item=event from=$events}
        {if $event.alldayevent != true}
            {assign var="timestamp" value=$event.startTime}
        {else}
            {assign var="timestamp" value=""}
        {/if}
        <ul class="pc_blocklist">
            {if $smarty.foreach.eventloop.iteration eq 1}
                <li class="pc_blockdate">
                    {$date|pc_date_format}
                </li>
            {/if}
            <li class="pc_blockevent">
                {pc_popup bgcolor=$event.catcolor caption=$event.title text=$event.hometext|safetext assign="javascript"}
                {gt text='private event' assign='p_txt'}
                {if $event.privateicon}{img src='locked.png' modname='core' set='icons/extrasmall' title=$p_txt alt=$p_txt}{/if}                        {pc_url full=true class="eventlink" action="detail" eid=$event.eid date=$date javascript=$javascript display="$timestamp `$event.title`"|strip_tags}
                {if $event.alldayevent != true}&nbsp;({gt text='until'} {$event.endTime}){/if}
                {if $event.commentcount gt 0}
                    {gt text='%s comment left' plural='%s comments left.' count=$event.commentcount tag1=$event.commentcount assign="title"}
                    <a href="{modurl modname='PostCalendar' func='view' viewtype='details' eid=$event.eid}#comments" title='{$title}'>
                    {gt text='Comment' assign='alt'}
                    {img modname=core src=comment.gif set=icons/extrasmall alt=$alt title=$title}</a>
                {/if}
            </li>
        </ul>
    {counter}
    {/foreach}
{/foreach}

{if $eventcount == 0}
    {gt text='No upcoming events.'}
{/if}
</div>