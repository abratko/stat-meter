CREATE MATERIALIZED VIEW if not exists CompanyEventSumming (
    `year` UInt16,
    `month` UInt8,
    `dayOfMonth` UInt8,
    `quartet` UInt8,
    `isoWeek` UInt8,
    `companyId` UInt32,
    `clickTgb` UInt16,
    `showTgb` UInt16,
    `clickBanner` UInt16,
    `showBanner` UInt16,
    `clickBranding` UInt16,
    `showBranding` UInt16,
    `showPhone` UInt16,
    `showCompanyPage` UInt16,
    `visitSiteFromCompanyPage` UInt16,
    `showInSearchList` UInt16,
    `visitSiteFromSearchList` UInt16,
    `showSite` UInt16
)
    ENGINE = SummingMergeTree
    PARTITION BY (year * 100) + month
    ORDER BY (year, quartet, month, isoWeek, dayOfMonth, companyId)
    SETTINGS index_granularity = 8192
    POPULATE AS
SELECT toYear(ce.eventDateTimeOnClient)                                                     AS year,
       toMonth(ce.eventDateTimeOnClient)                                                    AS month,
       toDayOfMonth(ce.eventDateTimeOnClient)                                               AS dayOfMonth,
       toQuarter(ce.eventDateTimeOnClient)                                                  AS quartet,
       toISOWeek(ce.eventDateTimeOnClient)                                                  AS isoWeek,
       ce.companyId,
       if(and (ce.tgbClick = 1, ce.tgbIsBanner = 0, ce.tgbPosition != 'layout_full'), 1, 0) as clickTgb,
       if(and (ce.tgbView = 1, ce.tgbIsBanner = 0, ce.tgbPosition != 'layout_full'), 1, 0)  as showTgb,
       if(and (ce.tgbClick = 1, ce.tgbIsBanner = 1, ce.tgbPosition != 'layout_full'), 1, 0) as clickBanner,
       if(and (ce.tgbView = 1, ce.tgbIsBanner = 1, ce.tgbPosition != 'layout_full'), 1, 0)  as showBanner,
       if(and (ce.tgbClick = 1, ce.tgbPosition = 'layout_full'), 1, 0)                      as clickBranding,
       if(and (ce.tgbView = 1, ce.tgbPosition = 'layout_full'), 1, 0)                       as showBranding,
       ce.showPhone,
       ce.showCompanyPage,
       ce.visitSiteFromCompanyPage,
       if(and (ce.showCompanyCardInList = 1, or(listType is null, listType = 2)), 1, 0)     AS showInSearchList,
       if(and (ce.visitSiteFromCompanyCard = 1, or(listType is null, listType = 2)), 1, 0)  AS visitSiteFromSearchList,
       ce.showSite
FROM CompanyEvent AS ce;
