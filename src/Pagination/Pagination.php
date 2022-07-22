<?php
namespace DeltaTools\Pagination;

class Pagination
{
    private $url = '';
    private $numberOfResults = 0;
    private $options = [
        'numberOfResults' => 25,
        'wrapOn' => 5,
        'prefix' => '',
        'prefix-exceptions' => [],
        'prefix-exceptions-sub' => [],
    ];

    private $searchProps = [
        'strona' => 1,
        'ilosc-na-stronie' => 25
    ];

    public function __construct(string $url, int $numberOfResults, array $searchProps = [], array $options = [])
    {
        $this->url = $url;
        $this->numberOfResults = $numberOfResults;
        
        $this->setSearchProps($searchProps);
        $this->setOptions($options);

        $this->numberOfPages = ceil($this->numberOfResults / $this->searchProps['ilosc-na-stronie']);
        //$this->addPropsToUrl();
    }

    private function setSearchProps($searchProps)
    {
        foreach($searchProps as $searchPropName => $searchPropValue){
            $this->searchProps[$searchPropName] = $searchPropValue;
        }
    }

    private function setOptions($options)
    {
        foreach($options as $optionName => $optionValue){
            $this->options[$optionName] = $optionValue;
        }
    }

    public function render()
    {

        $pagination = '';
        $prevWrapDone = false;
        $nextWrapDone = false;
        for ($i = 1; $i <= $this->numberOfPages; $i++) {

            if ($i != 1 & $i != $this->numberOfPages && $i <= ($this->searchProps['strona'] - $this->options['wrapOn']) && !$prevWrapDone){
                $pagination .= '<div class="sl-pagination-item">...</div>';
                $prevWrapDone = true;
            }

            if ($i == 1 || $i == $this->numberOfPages || ($i > ($this->searchProps['strona'] - $this->options['wrapOn']) && $i < ($this->searchProps['strona'] + $this->options['wrapOn']))) {
                $pagination .= "<div class='sl-pagination-item ";
                if($this->searchProps['strona'] == $i){
                    $pagination .= "sl-pagination-current-page";
                }
                $pagination .= "'><a href='".$this->getPageLink($i)."'>$i</a></div>";
            }

            if ($i != 1 & $i != $this->numberOfPages && $i >= ($this->searchProps['strona'] + $this->options['wrapOn']) && !$nextWrapDone){
                $pagination .= '<div class="sl-pagination-item">...</div>';
                $nextWrapDone = true;
            }

        }
        echo "<div class='sl-pagination'>$pagination</div>";

    }

    private function getPrefix($propName)
    {
        $key = array_search($propName, $this->options['prefix-exceptions']);
        if($key !== false){
            return $this->options['prefix-exceptions-sub'][$key];
        }

        return $this->options['prefix'];
    }

    private function getPageLink($pageNumber)
    {
        $link = $this->url;
        $urlBuilder = new \DeltaTools\Url\Builder($this->url);

        // $sign = '?';
        // if(strpos($this->url, '?') !== false){
        //     $sign = '&';
        // }
        $appendedProps = [];

        //set all current object props
        foreach($this->searchProps as $propName => $propValue){

            $prefix = $this->getPrefix($propName);

            if($propName == 'strona'){
                continue;
            }

            

            if(is_array($propValue)){
                foreach($propValue as $value){
                    $urlBuilder->setParam($propName . '[]', $value);
                    // $link .= $sign . $prefix . $propName . '[]=' . $value;
                    // $sign = '&';
                }
                $appendedProps[] = $prefix . $propName;
            }else{
                $urlBuilder->setParam($propName, $propValue);
                //$link .= $sign . $prefix . $propName . '=' . $propValue;
                $appendedProps[] = $prefix . $propName;
                //$sign = '&';
            }
            
            
        }

        $urlBuilder->setParam( $this->options['prefix'] . 'strona', $pageNumber);
        //$link .= $sign . $this->options['prefix'] . 'strona=' . $pageNumber;
        $appendedProps[] = $this->options['prefix'] . 'strona';
        //print_r($appendedProps);

        //add props from url
        foreach($_GET as $getName => $getValue){
            if(in_array($getName, $appendedProps)){
                continue;
            }

            if(is_array($getValue)){
                foreach($getValue as $value){
                    $urlBuilder->setParam($getName . '[]', $value);
                    // $link .= $sign . $getName . '[]=' . $value;
                    // $sign = '&';
                }
            }else{
                $urlBuilder->setParam($getName, $getValue);
                // $link .= $sign . $getName . '=' . $getValue;
                // $sign = '&';
            }
            
        }

        return $urlBuilder->getUrl();//$link;
    }

    public function getSortLink($linkName, $sortField)
    {

        $urlBuilder = new \DeltaTools\Url\Builder($this->url);

        $appendedProps = [];
        //add values to be skipped
        $appendedProps[] = $this->getPrefix('strona') . 'strona';
        $appendedProps[] = $this->getPrefix('sortowanie') . 'sortowanie';
        $appendedProps[] = $this->getPrefix('kierunek-sortowania') . 'kierunek-sortowania';


        foreach($this->searchProps as $propName => $propValue){
            
            $prefix = $this->getPrefix($propName);
            $propName = $prefix . $propName;
            
            if( in_array($propName, $appendedProps) ){
                continue;
            }

            if(is_array($propValue)){
                foreach($propValue as $value){
                    $urlBuilder->setParam($propName . '[]', $value);
                }
                $appendedProps[] = $propName;
            }else{
                $urlBuilder->setParam($propName, $propValue);
                $appendedProps[] = $propName;
            }
        }

        //add props from url
        foreach($_GET as $getName => $getValue){

            if(in_array($getName, $appendedProps)){
                continue;
            }

            if(is_array($getValue)){
                foreach($getValue as $value){
                    $urlBuilder->setParam($getName . '[]', $value);
                }
            }else{
                $urlBuilder->setParam($getName, $getValue);
            }
            
        }

        $urlBuilder->setParam( $this->options['prefix'] . 'sortowanie', $sortField);
        $urlBuilder->setParam( $this->options['prefix'] . 'strona', 1);

        if($this->searchProps['sortowanie'] == $sortField){
            if($this->searchProps['kierunek-sortowania'] == 'ASC'){
                $urlBuilder->setParam( $this->options['prefix'] . 'kierunek-sortowania', 'DESC');
                $link = '<a class="sl-sort-link sl-sort-link-asc" href="'.$urlBuilder->getUrl().'">'.$linkName.'</a>';
            }else{
                $urlBuilder->setParam( $this->options['prefix'] . 'kierunek-sortowania', 'ASC');
                $link = '<a class="sl-sort-link sl-sort-link-desc" href="'.$urlBuilder->getUrl().'">'.$linkName.'</a>';
            }
        }else{
            $urlBuilder->setParam( $this->options['prefix'] . 'kierunek-sortowania', 'ASC');
            $link = '<a class="sl-sort-link" href="'.$urlBuilder->getUrl().'">'.$linkName.'</a>';
        }
        
        return $link;
    }

}