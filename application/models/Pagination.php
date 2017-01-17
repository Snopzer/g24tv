<?php

// This is a helper class to make paginating
// records easy.
class Model_Pagination {

  public $current_page;
  public $per_page;
  public $total_count;

  public function __construct($page=1, $per_page=20, $total_count=0){
  	$this->current_page = (int)$page;
    $this->per_page = (int)$per_page;
    $this->total_count = (int)$total_count;
	//$this->pagination();
  }

  public function offset() {
    // Assuming 20 items per page:
    // page 1 has an offset of 0    (1-1) * 20
    // page 2 has an offset of 20   (2-1) * 20
    //   in other words, page 2 starts with item 21
    return ($this->current_page - 1) * $this->per_page;
  }

  public function total_pages() {
      return ceil($this->total_count/$this->per_page);
	}

  public function previous_page() {
    return $this->current_page - 1;
  }

  public function next_page() {
    return $this->current_page + 1;
  }

   public function first_page() {
    return 1;
  }

   public function last_page() {
    return $this->total_pages();
  }

	public function has_previous_page() {
		return $this->previous_page() >= 1 ? true : false;
	}

	public function has_next_page() {
		return $this->next_page() <= $this->total_pages() ? true : false;
	}


public function pagination($url)
	{
	//exit("in page");
 		//$this->view->page_offset=$pagination->offset();
 		if($this->total_pages() > 1)
		{
			$pagin=$pagin.'<div class="pagination">';
			if($_REQUEST['page']!="1" || $_REQUEST['page']!="") //first
			{
			$pagin=$pagin.'<a href="'.$url.'?page=1&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'].'" ><span> First</span></a>';
			}else
			{
				$pagin=$pagin.'<a href="#" ><span > First</span></a>';
			}

			if($this->has_previous_page()) //previous
			{
				$prev_page=$url.'?page='.$this->previous_page().'&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'];
				$pagin=$pagin.' <a href="'.$prev_page.'"><span> Prev</span></a>';
			}else
			{
				$prev_page="#";
				$pagin=$pagin.' <a href="'.$prev_page.'" ><span > Prev</span></a>';
			}


			if($this->has_next_page()) //next
			{
				$next_page=$url.'?page='.$this->next_page().'&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'];
			$pagin=$pagin.'<a href="'.$next_page.'"><span>Next </span></a>';
			}else
			{
				$next_page="#";
				$pagin=$pagin.'<a href="'.$next_page.'" class="button"><span >Next </span></a>';
			}


			if($_REQUEST['page']!=$this->total_pages()) //last
			{
			$pagin=$pagin.'<a href="'.$url.'?page='.$this->total_pages().'&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'].'" ><span>Last </span></a>';
			}else
			{
 				$pagin=$pagin.'<a href="#" class="button last"><span style="color:#ccc;">Last </span></a>';
			}

			$pagin=$pagin.'<div style="clear: both;"></div></div>';

			return $pagin;
		}

	}



	public function PageAddPar($arr)
	{
		$join="&";
		foreach($arr as $k=>$v)
		{
			$par=$par.$join.$k."=".$v;
		}
		return $par;
	}

	public function paginationP($url,$arr)
	{
		//exit("in page p");
		if($this->total_pages() > 1)
		{
			$pagin=$pagin.'<div class="pagination">';
			if($_REQUEST['page']!="1" || $_REQUEST['page']!="") //first
			{
				$pagin=$pagin.'<a href="'.$url.'?page=1&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'].$this->PageAddPar($arr).'" ><span> First</span></a>';
			}else
			{
				$pagin=$pagin.'<a href="#" ><span >First</span></a>';
			}

			if($this->has_previous_page()) //previous
			{
				$prev_page=$url.'?page='.$this->previous_page().'&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'].$this->PageAddPar($arr);
				$pagin=$pagin.' <a href="'.$prev_page.'"><span>Prev</span></a>';
			}else
			{
				$prev_page="#";
				$pagin=$pagin.' <a href="'.$prev_page.'" ><span > Prev</span></a>';
			}



			if($this->has_next_page()) //next
			{
				$next_page=$url.'?page='.$this->next_page().'&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'].$this->PageAddPar($arr);
				$pagin=$pagin.'<a href="'.$next_page.'" ><span>Next </span></a>';
			}
			else
			{
				$next_page="#";
				$pagin=$pagin.'<a href="'.$next_page.'" ><span >Next </span></a>';
			}


			if($_REQUEST['page']!=$this->total_pages()) //last
			{
				$pagin=$pagin.'<a href="'.$url.'?page='.$this->total_pages().'&disType='.$_REQUEST['disType'].'&sortby='.$_REQUEST['sortby'].$this->PageAddPar($arr).'" ><span>Last </span></a>';
			}else
			{
				$pagin=$pagin.'<a href="#" ><span >Last </span></a>';
			}
			$pagin=$pagin.'<div style="clear: both;"></div></div>';

			return $pagin;
		}

	}
}

?>