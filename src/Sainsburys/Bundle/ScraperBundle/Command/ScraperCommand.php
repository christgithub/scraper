<?php

namespace Sainsburys\Bundle\ScraperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Sainsburys\Bundle\ScraperBundle\Model\Item;

/**
 * Console command uses service ScraperManager to scrape a grocery web page.
 * It follows all the grocery items links on the page in order to fetch the title,the price per unit
 * and the description of each product. The result return by the service is a Json string listing all the items
 * as well as the total all the unit_price listed.   
 */
class ScraperCommand extends ContainerAwareCommand {

    /**
     * Sets the console commmand
     */
    protected function configure() {
        $this->setName('scraper:scraper')
                ->setDescription('Scrape a web page');
    }

    /**
     * This is the entry point method of the console command.
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $scraperManager = $this->getApplication()->getKernel()->getContainer()->get('scraper.manager');
        $crawler = $scraperManager->setUrl();
        $arrayLinks = $scraperManager->getMainPageLinks($crawler);

        foreach ($arrayLinks as $textLink) {
            list($text) = $textLink;
            $link = $scraperManager->getLink($crawler, $text);
            $scraperManager->add(
                    new Item(
                        $scraperManager->getItemProperty($link, Item::EXP_TITLE), 
                        $scraperManager->getItemProperty($link, Item::EXP_DESC), 
                        $scraperManager->getItemProperty($link, Item::EXP_PRICE),
                        $scraperManager->getItemDOMSize($link)    
                    )
            );
        }
        $output->writeln($scraperManager->getJson());
    }
}
