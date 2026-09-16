@if( config('app.locale_switcher_enabled') && count(config('app.locale_active')) > 1 )
    @if( config('app.locale_switcher_style') == 'dropdown' )

        <div class="language-switcher language-switcher-style-dropdown">
            <div class="btn-group">
                <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false" @if( in_array(config('app.locale_switcher_display'), ['flags']) ) title="{{ config('app.locales')[app()->getLocale()]['native'] }}" @endif>
                    @if( in_array(config('app.locale_switcher_display'), ['names_flags', 'flags']) )
                        <img src="{{ asset_url('images','flags/'. app()->getLocale() .'.png') }}" class="language-flag" />
                    @else
                        <i class="fa fa-globe language-icon"></i>
                    @endif
                    @if( in_array(config('app.locale_switcher_display'), ['names_flags', 'names']) )
                        @if( config('app.locale_switcher_display_name_code') )
                            <span class="language-name">{{ explode('-', config('app.locales')[app()->getLocale()]['code'])[1] }}</span>
                        @else
                            <span class="language-name">{{ config('app.locales')[app()->getLocale()]['native'] }}</span>
                        @endif
                    @endif
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    @foreach (config('app.locales') as $lang => $language)
                        @if( in_array($lang, config('app.locale_active')) )
                            <li>
                                <a href="{{ route('locale.change', $lang) }}" @if( in_array(config('app.locale_switcher_display'), ['flags']) ) title="{{ $language['native'] }}" @endif class="clearfix">
                                    @if( in_array(config('app.locale_switcher_display'), ['names_flags', 'flags']) )
                                        <img src="{{ asset_url('images','flags/'. $lang .'.png') }}" class="language-flag" />
                                    @endif
                                    @if( in_array(config('app.locale_switcher_display'), ['names_flags', 'names']) )
                                        @if( config('app.locale_switcher_display_name_code') )
                                            <span class="language-name">{{ explode('-', $language['code'])[1] }}</span>
                                        @else
                                            <span class="language-name">{{ $language['native'] }}</span>
                                        @endif
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

    @else

        <div class="language-switcher language-switcher-style-inline">
            <ul class="list-inline">
                @foreach (config('app.locales') as $lang => $language)
                    @if( in_array($lang, config('app.locale_active')) )
                        <li @if ($lang == app()->getLocale()) class="active" @endif>
                            <a href="{{ route('locale.change', $lang) }}" title="{{ $language['native'] }}">
                                @if( in_array(config('app.locale_switcher_display'), ['names_flags', 'flags']) )
                                    <img src="{{ asset_url('images','flags/'. $lang .'.png') }}" class="language-flag" />
                                @endif
                                @if( in_array(config('app.locale_switcher_display'), ['names_flags', 'names']) )
                                    @if( config('app.locale_switcher_display_name_code') )
                                        <span class="language-name">{{ explode('-', $language['code'])[1] }}</span>
                                    @else
                                        <span class="language-name">{{ $language['native'] }}</span>
                                    @endif
                                @endif
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

    @endif
@endif
